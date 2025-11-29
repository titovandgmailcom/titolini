<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ОСНОВНЫЕ ФУНКЦИИ СИСТЕМЫ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log');

// ═══════════════════════════════════════════════════════════
// СИСТЕМА ЛОЯЛЬНОСТИ
// ═══════════════════════════════════════════════════════════

/**
 * Получить карту лояльности пользователя
 */
function getLoyaltyCard($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM loyalty_cards WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Рассчитать уровень лояльности по сумме покупок
 */
function calculateLoyaltyLevel($total_spent) {
    if ($total_spent >= 70001) return 'platinum';
    if ($total_spent >= 30001) return 'gold';
    if ($total_spent >= 10001) return 'silver';
    return 'bronze';
}

/**
 * Обновить уровень лояльности
 */
function updateLoyaltyLevel($user_id) {
    global $pdo;
    
    $card = getLoyaltyCard($user_id);
    if (!$card) return false;
    
    $old_level = $card['current_level'];
    $new_level = calculateLoyaltyLevel($card['total_spent']);
    
    if ($new_level !== $old_level) {
        $stmt = $pdo->prepare("UPDATE loyalty_cards SET current_level = ? WHERE user_id = ?");
        $stmt->execute([$new_level, $user_id]);
        
        // Начислить бонус за повышение уровня
        $bonus_amounts = [
            'silver' => 500,
            'gold' => 1000,
            'platinum' => 2500
        ];
        
        if (isset($bonus_amounts[$new_level])) {
            addBonusPoints(
                $user_id, 
                $bonus_amounts[$new_level], 
                "Бонус за достижение уровня " . LOYALTY_LEVELS[$new_level]['name']
            );
        }
        
        return $new_level;
    }
    
    return false;
}

/**
 * Начислить бонусные баллы
 */
function addBonusPoints($user_id, $amount, $description, $order_id = null, $type = 'earn') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Добавить транзакцию
        $stmt = $pdo->prepare("
            INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $type, $amount, $description, $order_id]);
        
        // Обновить баланс
        $stmt = $pdo->prepare("
            UPDATE loyalty_cards 
            SET points_balance = points_balance + ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $user_id]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Add Bonus Points Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Списать бонусные баллы
 */
function spendBonusPoints($user_id, $amount, $description, $order_id = null) {
    global $pdo;
    
    $card = getLoyaltyCard($user_id);
    
    if (!$card || $card['points_balance'] < $amount) {
        return false;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Добавить транзакцию списания
        $stmt = $pdo->prepare("
            INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
            VALUES (?, 'spend', ?, ?, ?)
        ");
        $stmt->execute([$user_id, -$amount, $description, $order_id]);
        
        // Обновить баланс
        $stmt = $pdo->prepare("
            UPDATE loyalty_cards 
            SET points_balance = points_balance - ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $user_id]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Spend Bonus Points Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Получить процент кешбэка для уровня
 */
function getCashbackPercent($user_id) {
    $card = getLoyaltyCard($user_id);
    if (!$card) return 1;
    
    return LOYALTY_LEVELS[$card['current_level']]['cashback_percent'];
}

/**
 * Рассчитать кешбэк за заказ
 */
function calculateCashback($user_id, $order_amount) {
    $percent = getCashbackPercent($user_id);
    return round($order_amount * ($percent / 100), 2);
}

// ═══════════════════════════════════════════════════════════
// КОЛЕСО ФОРТУНЫ
// ═══════════════════════════════════════════════════════════

/**
 * Проверить доступность вращения колеса
 */
function canSpinWheel($user_id) {
    global $pdo;
    
    $card = getLoyaltyCard($user_id);
    if (!$card) return ['can_spin' => false, 'reason' => 'Карта лояльности не найдена'];
    
    $daily_spins = LOYALTY_LEVELS[$card['current_level']]['daily_spins'];
    
    // Подсчитать вращения сегодня
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM wheel_spins 
        WHERE user_id = ? AND spin_date = CURDATE()
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    $spins_today = $result['count'];
    $spins_left = $daily_spins - $spins_today;
    
    if ($spins_left > 0) {
        return ['can_spin' => true, 'spins_left' => $spins_left];
    }
    
    return ['can_spin' => false, 'reason' => 'Лимит вращений исчерпан. Возвращайтесь завтра!'];
}

/**
 * Крутить колесо фортуны
 */
function spinWheel($user_id) {
    global $pdo;
    
    $check = canSpinWheel($user_id);
    if (!$check['can_spin']) {
        return ['success' => false, 'error' => $check['reason']];
    }
    
    // Выбрать приз на основе вероятности
    $stmt = $pdo->query("SELECT * FROM wheel_prizes WHERE is_active = 1");
    $prizes = $stmt->fetchAll();
    
    $prize = selectRandomPrize($prizes);
    
    if (!$prize) {
        return ['success' => false, 'error' => 'Ошибка при выборе приза'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Записать вращение
        $stmt = $pdo->prepare("
            INSERT INTO wheel_spins (user_id, prize_id, spin_date)
            VALUES (?, ?, CURDATE())
        ");
        $stmt->execute([$user_id, $prize['id']]);
        
        // Начислить приз
        if ($prize['type'] === 'bonus') {
            addBonusPoints($user_id, $prize['value'], "Выигрыш в колесе фортуны: " . $prize['name']);
        }
        // TODO: Обработка других типов призов (купоны, подарки, доставка)
        
        $pdo->commit();
        
        return [
            'success' => true,
            'prize' => $prize,
            'spins_left' => $check['spins_left'] - 1
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Wheel Spin Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при вращении колеса'];
    }
}

/**
 * Выбрать случайный приз на основе вероятности
 */
function selectRandomPrize($prizes) {
    $total_probability = array_sum(array_column($prizes, 'probability'));
    $random = mt_rand(1, $total_probability * 100) / 100;
    
    $cumulative = 0;
    foreach ($prizes as $prize) {
        $cumulative += $prize['probability'];
        if ($random <= $cumulative) {
            return $prize;
        }
    }
    
    return $prizes[0]; // Fallback
}

// ═══════════════════════════════════════════════════════════
// ЭКО-ПРОГРАММА
// ═══════════════════════════════════════════════════════════

/**
 * Сканировать QR код
 */
function scanQRCode($user_id, $code_hash) {
    global $pdo;
    
    // Проверить существование и статус QR кода
    $stmt = $pdo->prepare("SELECT * FROM qr_codes WHERE code_hash = ? AND is_scanned = 0");
    $stmt->execute([$code_hash]);
    $qr_code = $stmt->fetch();
    
    if (!$qr_code) {
        return ['success' => false, 'error' => 'QR код не найден или уже отсканирован'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Отметить QR код как отсканированный
        $stmt = $pdo->prepare("
            UPDATE qr_codes 
            SET is_scanned = 1, scanned_by = ?, scanned_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$user_id, $qr_code['id']]);
        
        // Начислить бонусы ВРУЧНУЮ (не вызываем addBonusPoints)
        $bonus_amount = ECO_POINTS_PER_QR;
        
        $stmt = $pdo->prepare("
            INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
            VALUES (?, 'eco', ?, ?, NULL)
        ");
        $stmt->execute([$user_id, $bonus_amount, "Сканирование QR кода эко-упаковки"]);
        
        $stmt = $pdo->prepare("
            UPDATE loyalty_cards 
            SET points_balance = points_balance + ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$bonus_amount, $user_id]);
        
        // Проверить достижения ПОСЛЕ commit
        $pdo->commit();
        
        // Теперь проверяем достижения (вне транзакции)
        $achievement = checkEcoAchievementSafe($user_id);
        
        return [
            'success' => true,
            'bonus' => $bonus_amount,
            'achievement' => $achievement
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("QR Scan Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при сканировании'];
    }
}

/**
 * Проверить эко-достижения (безопасная версия с собственной транзакцией)
 */
function checkEcoAchievementSafe($user_id) {
    global $pdo;
    
    try {
        // Подсчитать количество отсканированных QR кодов
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM qr_codes WHERE scanned_by = ?");
        $stmt->execute([$user_id]);
        $qr_count = $stmt->fetch()['count'];
        
        // Проверить каждый бейдж
        foreach (ECO_BADGES as $badge_type => $badge_data) {
            if ($qr_count >= $badge_data['qr_count']) {
                // Проверить, не получен ли уже этот бейдж
                $stmt = $pdo->prepare("
                    SELECT id FROM eco_achievements 
                    WHERE user_id = ? AND badge_type = ?
                ");
                $stmt->execute([$user_id, $badge_type]);
                
                if (!$stmt->fetch()) {
                    // Новая транзакция для достижения
                    $pdo->beginTransaction();
                    
                    // Выдать бейдж
                    $stmt = $pdo->prepare("
                        INSERT INTO eco_achievements (user_id, badge_type, bonus_received)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $badge_type, $badge_data['bonus']]);
                    
                    // Начислить бонус за бейдж ВРУЧНУЮ
                    $stmt = $pdo->prepare("
                        INSERT INTO loyalty_transactions (user_id, type, amount, description)
                        VALUES (?, 'eco', ?, ?)
                    ");
                    $stmt->execute([
                        $user_id, 
                        $badge_data['bonus'], 
                        "Достижение эко-программы: " . $badge_data['name']
                    ]);
                    
                    $stmt = $pdo->prepare("
                        UPDATE loyalty_cards 
                        SET points_balance = points_balance + ? 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$badge_data['bonus'], $user_id]);
                    
                    $pdo->commit();
                    
                    return [
                        'earned' => true,
                        'badge' => $badge_data,
                        'type' => $badge_type
                    ];
                }
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Eco Achievement Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Получить эко-статистику пользователя
 */
function getEcoStats($user_id) {
    global $pdo;
    
    // Количество отсканированных QR
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM qr_codes WHERE scanned_by = ?");
    $stmt->execute([$user_id]);
    $qr_count = $stmt->fetch()['count'];
    
    // Полученные бейджи
    $stmt = $pdo->prepare("SELECT badge_type, earned_at FROM eco_achievements WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $badges = $stmt->fetchAll();
    
    // Следующий бейдж
    $next_badge = null;
    foreach (ECO_BADGES as $badge_type => $badge_data) {
        $has_badge = false;
        foreach ($badges as $earned) {
            if ($earned['badge_type'] === $badge_type) {
                $has_badge = true;
                break;
            }
        }
        
        if (!$has_badge) {
            $next_badge = [
                'type' => $badge_type,
                'name' => $badge_data['name'],
                'required' => $badge_data['qr_count'],
                'current' => $qr_count,
                'remaining' => $badge_data['qr_count'] - $qr_count,
                'bonus' => $badge_data['bonus']
            ];
            break;
        }
    }
    
    return [
        'qr_scanned' => $qr_count,
        'badges_earned' => count($badges),
        'badges' => $badges,
        'next_badge' => $next_badge
    ];
}

// ═══════════════════════════════════════════════════════════
// ТОВАРЫ И КАТАЛОГ
// ═══════════════════════════════════════════════════════════

/**
 * Получить популярные товары
 */
function getPopularProducts($limit = 12) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE is_active = 1 AND in_stock > 0 
        ORDER BY featured DESC, created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Получить товары по категории
 */
function getProductsByCategory($category_id, $page = 1, $per_page = PRODUCTS_PER_PAGE) {
    global $pdo;
    $offset = ($page - 1) * $per_page;
    
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND is_active = 1 AND in_stock > 0 
        ORDER BY name 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$category_id, $per_page, $offset]);
    return $stmt->fetchAll();
}

/**
 * Получить товар по ID
 */
function getProductById($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

/**
 * Получить товар по slug
 */
function getProductBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Поиск товаров
 */
function searchProducts($query, $page = 1, $per_page = PRODUCTS_PER_PAGE) {
    global $pdo;
    $offset = ($page - 1) * $per_page;
    $search_term = "%$query%";
    
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE (name LIKE ? OR description LIKE ?) 
        AND is_active = 1 AND in_stock > 0 
        ORDER BY name 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$search_term, $search_term, $per_page, $offset]);
    return $stmt->fetchAll();
}

/**
 * Получить все категории
 */
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT * FROM categories 
        WHERE is_active = 1 AND parent_id IS NULL 
        ORDER BY sort_order, name
    ");
    return $stmt->fetchAll();
}

/**
 * Получить категорию по ID
 */
function getCategoryById($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
    $stmt->execute([$category_id]);
    return $stmt->fetch();
}

/**
 * Получить категорию по slug
 */
function getCategoryBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ═══════════════════════════════════════════════════════════
// КОРЗИНА
// ═══════════════════════════════════════════════════════════

/**
 * Добавить товар в корзину
 */
function addToCart($user_id, $product_id, $quantity = 1) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        $stmt->execute([$user_id, $product_id, $quantity, $quantity]);
        return true;
    } catch (Exception $e) {
        error_log("Add to Cart Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Получить корзину пользователя
 */
function getCart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.old_price, p.image_url, p.unit, p.weight,
               (c.quantity * p.price) as total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.is_active = 1
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Обновить количество товара в корзине
 */
function updateCartQuantity($user_id, $product_id, $quantity) {
    global $pdo;
    
    if ($quantity <= 0) {
        return removeFromCart($user_id, $product_id);
    }
    
    $stmt = $pdo->prepare("
        UPDATE cart SET quantity = ? 
        WHERE user_id = ? AND product_id = ?
    ");
    return $stmt->execute([$quantity, $user_id, $product_id]);
}

/**
 * Удалить товар из корзины
 */
function removeFromCart($user_id, $product_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

/**
 * Очистить корзину
 */
function clearCart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

/**
 * Получить количество товаров в корзине
 */
function getCartCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Получить общую сумму корзины
 */
function getCartTotal($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * p.price) as total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

// ═══════════════════════════════════════════════════════════
// ИЗБРАННОЕ
// ═══════════════════════════════════════════════════════════

/**
 * Добавить товар в избранное
 */
function addToFavorites($user_id, $product_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO favorites (user_id, product_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$user_id, $product_id]);
        return true;
    } catch (Exception $e) {
        error_log("Add to Favorites Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Удалить товар из избранного
 */
function removeFromFavorites($user_id, $product_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

/**
 * Проверить, в избранном ли товар
 */
function isInFavorites($user_id, $product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch() !== false;
}

/**
 * Получить избранные товары
 */
function getFavorites($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT f.*, p.*
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.user_id = ? AND p.is_active = 1
        ORDER BY f.added_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Получить количество избранных товаров
 */
function getFavoritesCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// ═══════════════════════════════════════════════════════════
// ЗАКАЗЫ
// ═══════════════════════════════════════════════════════════

/**
 * Создать заказ
 */
function createOrder($user_id, $order_data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Получить товары из корзины
        $cart_items = getCart($user_id);
        if (empty($cart_items)) {
            throw new Exception('Корзина пуста');
        }
        
        // Рассчитать суммы
        $total_amount = array_sum(array_column($cart_items, 'total'));
        $discount_amount = $order_data['discount_amount'] ?? 0;
        $bonus_used = $order_data['bonus_used'] ?? 0;
        $final_amount = $total_amount - $discount_amount - $bonus_used;
        
        // Генерировать номер заказа
        $order_number = generateOrderNumber();
        
        // Создать заказ
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, order_number, total_amount, discount_amount, 
                bonus_used, final_amount, delivery_address, delivery_date,
                delivery_time, payment_method, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $user_id,
            $order_number,
            $total_amount,
            $discount_amount,
            $bonus_used,
            $final_amount,
            $order_data['delivery_address'],
            $order_data['delivery_date'] ?? null,
            $order_data['delivery_time'] ?? null,
            $order_data['payment_method']
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Добавить позиции заказа
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
                $item['total']
            ]);
            
            // Уменьшить количество товара на складе
            $stmt = $pdo->prepare("
                UPDATE products SET in_stock = in_stock - ? WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // ПЕРЕД блоком генерации QR добавьте:
        error_log("=== BEFORE QR GENERATION === Order ID: $order_id");
        error_log("Cart items count: " . count($cart_items));
        error_log("Cart items: " . print_r($cart_items, true));


        // Генерировать QR-коды для товаров с упаковкой
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
                    error_log("QR Insert Error for order $order_id, product {$item['product_id']}: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
        
        error_log("Generated $qr_count QR codes for order $order_id");


        // Списать использованные бонусы
        if ($bonus_used > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
                VALUES (?, 'spend', ?, ?, ?)
            ");
            $stmt->execute([$user_id, -$bonus_used, "Оплата заказа №$order_number", $order_id]);
            
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET points_balance = points_balance - ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$bonus_used, $user_id]);
        }
        
        // Получить ТЕКУЩИЙ уровень и total_spent ДО обновления
        $card = getLoyaltyCard($user_id);
        $old_level = $card['current_level'];
        $old_total_spent = (float)$card['total_spent'];
        
        // Обновить total_spent
        $new_total_spent = $old_total_spent + $total_amount;
        $stmt = $pdo->prepare("
            UPDATE loyalty_cards 
            SET total_spent = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$new_total_spent, $user_id]);
        
        // Рассчитать НОВЫЙ уровень
        $new_level = calculateLoyaltyLevel($new_total_spent);
        
        // Начислить кешбэк по ТЕКУЩЕМУ уровню (до повышения)
        $cashback_percent = LOYALTY_LEVELS[$old_level]['cashback_percent'];
        $cashback = round($final_amount * ($cashback_percent / 100), 2);
        
        if ($cashback > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description, order_id)
                VALUES (?, 'earn', ?, ?, ?)
            ");
            $stmt->execute([$user_id, $cashback, "Кешбэк за заказ №$order_number", $order_id]);
            
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET points_balance = points_balance + ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$cashback, $user_id]);
        }
        
        // Если уровень изменился - обновить и начислить бонус ОДИН РАЗ
        if ($new_level !== $old_level) {
            $stmt = $pdo->prepare("
                UPDATE loyalty_cards 
                SET current_level = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$new_level, $user_id]);
            
            // Начислить бонус за повышение уровня ОДИН РАЗ
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
                    $user_id, 
                    $level_bonus, 
                    "Бонус за достижение уровня " . LOYALTY_LEVELS[$new_level]['name']
                ]);
                
                $stmt = $pdo->prepare("
                    UPDATE loyalty_cards 
                    SET points_balance = points_balance + ? 
                    WHERE user_id = ?
                ");
                $stmt->execute([$level_bonus, $user_id]);
            }
        }
        
        // Очистить корзину
        clearCart($user_id);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number
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
 * Генерировать номер заказа
 */
function generateOrderNumber() {
    return 'RU-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/**
 * Получить заказы пользователя
 */
function getUserOrders($user_id, $limit = null) {
    global $pdo;
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $limit]);
    } else {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Получить заказ по ID
 */
function getOrderById($order_id, $user_id = null) {
    global $pdo;
    
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
    }
    
    return $stmt->fetch();
}

/**
 * Получить позиции заказа
 */
function getOrderItems($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image_url, p.unit
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// ═══════════════════════════════════════════════════════════
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ═══════════════════════════════════════════════════════════

/**
 * Рассчитать скидку товара в процентах
 */
function calculateDiscount($old_price, $new_price) {
    if (!$old_price || $old_price <= $new_price) return 0;
    return round((($old_price - $new_price) / $old_price) * 100);
}

/**
 * Получить название статуса заказа
 */
function getOrderStatusName($status) {
    $statuses = [
        'pending' => 'Ожидает обработки',
        'confirmed' => 'Подтвержден',
        'processing' => 'Готовится',
        'shipped' => 'В доставке',
        'delivered' => 'Доставлен',
        'cancelled' => 'Отменен'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Получить цвет статуса заказа
 */
function getOrderStatusColor($status) {
    $colors = [
        'pending' => '#FF6B35',
        'confirmed' => '#6BBF59',
        'processing' => '#2196F3',
        'shipped' => '#9C27B0',
        'delivered' => '#4CAF50',
        'cancelled' => '#F44336'
    ];
    return $colors[$status] ?? '#999';
}

/**
 * Загрузить изображение
 */
function uploadImage($file, $target_dir = PRODUCT_IMAGE_DIR) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Ошибка при загрузке файла'];
    }
    
    // Проверка типа файла
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Недопустимый тип файла'];
    }
    
    // Проверка размера файла
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'error' => 'Файл слишком большой'];
    }
    
    // Генерация уникального имени файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $target_dir . $filename;
    
    // Создание директории если не существует
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Перемещение файла
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => $target_path];
    }
    
    return ['success' => false, 'error' => 'Не удалось сохранить файл'];
}