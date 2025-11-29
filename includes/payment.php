<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СИСТЕМА ОПЛАТЫ ЮKASSA
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';

// ВАШИ ДАННЫЕ ЮKASSA
define('YOOKASSA_SHOP_ID', '1179811');
define('YOOKASSA_SECRET_KEY', 'test_I4dp_fnyt0hLpNAIICYaYCtVE8WooBgD-reTpMQGIx8');

/**
 * Рассчитать максимально доступные бонусы для оплаты (до 70%)
 */
function calculateMaxBonusUsage($order_amount, $user_bonus_balance) {
    $max_bonus_percent = 0.7;
    $max_bonus_amount = $order_amount * $max_bonus_percent;
    return min($max_bonus_amount, $user_bonus_balance);
}

/**
 * Создать заказ с комбинированной оплатой
 */
function createOrderWithPayment($user_id, $cart_items, $bonus_to_use, $delivery_data, $payment_method = 'card') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        $card = getLoyaltyCard($user_id);
        if (!$card) {
            throw new Exception('Карта лояльности не найдена');
        }
        
        $user_bonus_balance = $card['points_balance'];
        $max_bonus = calculateMaxBonusUsage($total_amount, $user_bonus_balance);
        
        if ($bonus_to_use > $max_bonus) {
            throw new Exception('Недостаточно бонусов или превышен лимит использования');
        }
        
        $amount_to_pay = $total_amount - $bonus_to_use;
        $order_number = generateOrderNumber();
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, order_number, total_amount, bonus_used, 
                cloudpayments_amount, final_amount, payment_status, 
                delivery_address, delivery_date, delivery_time, 
                payment_method, status
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $user_id,
            $order_number,
            $total_amount,
            $bonus_to_use,
            $amount_to_pay,
            $amount_to_pay,
            $delivery_data['address'],
            $delivery_data['date'] ?? null,
            $delivery_data['time'] ?? null,
            $payment_method
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Добавить товары
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, total)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        // ГЕНЕРАЦИЯ QR-КОДОВ
        error_log("Generating QR codes for YooKassa order $order_id");
        $qr_count = 0;
        
        foreach ($cart_items as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $code_hash = md5($order_id . '_' . $item['product_id'] . '_' . $i . '_' . time() . '_' . SECRET_KEY);
                
                $stmt = $pdo->prepare("
                    INSERT INTO qr_codes (order_id, product_id, code_hash)
                    VALUES (?, ?, ?)
                ");
                
                if ($stmt->execute([$order_id, $item['product_id'], $code_hash])) {
                    $qr_count++;
                } else {
                    error_log("QR Insert Error: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
        
        error_log("Generated $qr_count QR codes for YooKassa order $order_id");
        
        // Списать бонусы если используются
        if ($bonus_to_use > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
                VALUES (?, 'spend', ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, 
                -$bonus_to_use, 
                "Оплата заказа №$order_number",
                $order_id
            ]);
            
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET points_balance = points_balance - ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$bonus_to_use, $user_id]);
            
            $stmt = $pdo->prepare("
                INSERT INTO payment_transactions (order_id, payment_method, amount, status, transaction_data)
                VALUES (?, 'bonus', ?, 'success', ?)
            ");
            $stmt->execute([
                $order_id,
                $bonus_to_use,
                json_encode(['bonus_used' => $bonus_to_use])
            ]);
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total_amount' => $total_amount,
            'bonus_used' => $bonus_to_use,
            'amount_to_pay' => $amount_to_pay
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Create Order Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Создать платеж в ЮKassa
 */
function createYooKassaPayment($order_id, $amount, $order_number, $return_url) {
    $url = 'https://api.yookassa.ru/v3/payments';
    
    $data = [
        'amount' => [
            'value' => number_format($amount, 2, '.', ''),
            'currency' => 'RUB'
        ],
        'capture' => true,
        'confirmation' => [
            'type' => 'redirect',
            'return_url' => $return_url
        ],
        'description' => "Оплата заказа №$order_number",
        'metadata' => [
            'order_id' => $order_id,
            'order_number' => $order_number
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Idempotence-Key: ' . uniqid('', true)
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, YOOKASSA_SHOP_ID . ':' . YOOKASSA_SECRET_KEY);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("YooKassa Error: " . $response);
        return ['success' => false, 'error' => 'Ошибка создания платежа'];
    }
    
    $result = json_decode($response, true);
    
    return [
        'success' => true,
        'payment_id' => $result['id'],
        'confirmation_url' => $result['confirmation']['confirmation_url']
    ];
}

/**
 * Проверить статус платежа
 */
function checkYooKassaPayment($payment_id) {
    $url = "https://api.yookassa.ru/v3/payments/$payment_id";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, YOOKASSA_SHOP_ID . ':' . YOOKASSA_SECRET_KEY);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Обработка успешного платежа
 * С ПОЛНОЙ ОТЛАДКОЙ
 */
function handlePaymentSuccess($order_id, $payment_id, $amount) {
    global $pdo;
    
    error_log("=== PAYMENT SUCCESS START ===");
    error_log("Order ID: $order_id, Payment ID: $payment_id, Amount: $amount");
    
    try {
        $pdo->beginTransaction();
        
        // 1. Обновить статус заказа
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', transaction_id = ?, status = 'confirmed'
            WHERE id = ?
        ");
        $stmt->execute([$payment_id, $order_id]);
        error_log("✓ Order status updated");
        
        // 2. Записать транзакцию оплаты
        $stmt = $pdo->prepare("
            INSERT INTO payment_transactions (order_id, payment_method, amount, status, transaction_data)
            VALUES (?, 'yookassa', ?, 'success', ?)
        ");
        $stmt->execute([
            $order_id,
            $amount,
            json_encode(['payment_id' => $payment_id])
        ]);
        error_log("✓ Payment transaction recorded");
        
        // 3. Получить данные заказа
        $stmt = $pdo->prepare("
            SELECT user_id, total_amount, bonus_used, final_amount, order_number 
            FROM orders 
            WHERE id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception("Order $order_id not found");
        }
        
        error_log("Order data: user_id={$order['user_id']}, total={$order['total_amount']}, final={$order['final_amount']}");
        
        // 4. Начислить кешбэк (ВНУТРИ ЭТОЙ ЖЕ ТРАНЗАКЦИИ)
        $paid_amount = floatval($order['final_amount']);
        $cashback = calculateCashback($order['user_id'], $paid_amount);
        
        error_log("Calculated cashback: $cashback");
        
        if ($cashback > 0) {
            // НЕ ВЫЗЫВАЕМ addBonusPoints, делаем ВСЁ ВРУЧНУЮ
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
                VALUES (?, 'earn', ?, ?, ?)
            ");
            $stmt->execute([
                $order['user_id'],
                $cashback,
                "Кешбэк за заказ №{$order['order_number']}",
                $order_id
            ]);
            
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET points_balance = points_balance + ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$cashback, $order['user_id']]);
            
            error_log("✓ Cashback $cashback added");
        }
        
        // 5. Обновить total_spent
        $stmt = $pdo->prepare("
            UPDATE loyalty_cards 
            SET total_spent = total_spent + ?
            WHERE user_id = ?
        ");
        $stmt->execute([$order['total_amount'], $order['user_id']]);
        error_log("✓ Total spent updated by {$order['total_amount']}");
        
        // 6. Проверить и обновить уровень лояльности (ВРУЧНУЮ, БЕЗ ВЫЗОВА ФУНКЦИИ)
        $card = getLoyaltyCard($order['user_id']);
        $new_level = calculateLoyaltyLevel($card['total_spent']);
        
        if ($new_level !== $card['current_level']) {
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET current_level = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$new_level, $order['user_id']]);
            
            error_log("✓ Level updated to: $new_level");
            
            // Начислить бонус за повышение уровня
            $bonus_amounts = [
                'silver' => 500,
                'gold' => 1000,
                'platinum' => 2500
            ];
            
            if (isset($bonus_amounts[$new_level])) {
                $level_bonus = $bonus_amounts[$new_level];
                
                $stmt = $pdo->prepare("
                    INSERT INTO loyalty_transactions (user_id, type, amount, description)
                    VALUES (?, 'bonus', ?, ?)
                ");
                $stmt->execute([
                    $order['user_id'],
                    $level_bonus,
                    "Бонус за достижение уровня " . LOYALTY_LEVELS[$new_level]['name']
                ]);
                
                $stmt = $pdo->prepare("
                    UPDATE loyalty_cards 
                    SET points_balance = points_balance + ? 
                    WHERE user_id = ?
                ");
                $stmt->execute([$level_bonus, $order['user_id']]);
                
                error_log("✓ Level bonus $level_bonus added");
            }
        }
        
        // 7. Очистить корзину
        clearCart($order['user_id']);
        error_log("✓ Cart cleared");
        
        // ОДИН commit для ВСЕЙ транзакции
        $pdo->commit();
        error_log("=== PAYMENT SUCCESS COMPLETE ===");
        
        return ['success' => true];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("✗ PAYMENT ERROR: " . $e->getMessage());
        error_log("Stack: " . $e->getTraceAsString());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Обработка неудачного платежа
 */
function handlePaymentFailed($order_id, $reason) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $stmt = $pdo->prepare("SELECT user_id, bonus_used FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if ($order && $order['bonus_used'] > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
                VALUES (?, 'refund', ?, ?, ?)
            ");
            $stmt->execute([
                $order['user_id'],
                $order['bonus_used'],
                "Возврат бонусов за неудачную оплату #$order_id",
                $order_id
            ]);
            
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET points_balance = points_balance + ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$order['bonus_used'], $order['user_id']]);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO payment_transactions (order_id, payment_method, amount, status, transaction_data)
            VALUES (?, 'yookassa', 0, 'failed', ?)
        ");
        $stmt->execute([$order_id, json_encode(['reason' => $reason])]);
        
        $pdo->commit();
        
        return ['success' => true];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Payment Failed Handler Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}