<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ОФОРМЛЕНИЕ ЗАКАЗА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

// Подключаем необходимые файлы ДО header.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Проверка авторизации
if (!isLoggedIn()) {
    setFlash('info', 'Для оформления заказа необходимо войти в систему');
    redirect('/login.php?redirect=/checkout.php');
    exit;
}

// Получить корзину
$cart_items = getCart($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);

if (empty($cart_items)) {
    setFlash('info', 'Ваша корзина пуста');
    redirect('/catalog.php');
    exit;
}

$loyalty_card = getLoyaltyCard($_SESSION['user_id']);
$user = getCurrentUser();

// ОБРАБОТКА POST ЗАПРОСА ДО ПОДКЛЮЧЕНИЯ HEADER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/payment.php';
    
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $delivery_date = $_POST['delivery_date'] ?? null;
    $delivery_time = $_POST['delivery_time'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $use_bonuses = isset($_POST['use_bonuses']) ? (float)$_POST['use_bonuses'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    
    $errors = [];
    
    if (empty($delivery_address)) {
        $errors[] = 'Укажите адрес доставки';
    }
    
    if ($use_bonuses > $loyalty_card['points_balance']) {
        $errors[] = 'Недостаточно бонусов';
    }
    
    if (empty($errors)) {
        $delivery_data = [
            'address' => $delivery_address,
            'date' => $delivery_date,
            'time' => $delivery_time
        ];
        
        // Если выбрана онлайн-оплата - использовать ЮKassa
        if ($payment_method === 'online') {
            $result = createOrderWithPayment(
                $_SESSION['user_id'],
                $cart_items,
                $use_bonuses,
                $delivery_data,
                'card'
            );
            
            if ($result['success']) {
                $_SESSION['pending_order'] = $result;
                
                // Если полностью оплачено бонусами
                if ($result['amount_to_pay'] <= 0) {
                    handlePaymentSuccess($result['order_id'], 'BONUS_ONLY', 0);
                    setFlash('success', 'Заказ успешно оформлен и оплачен бонусами!');
                    redirect('/customer/orders.php');
                } else {
                    // Перенаправить на страницу оплаты ЮKassa
                    redirect('/customer/payment.php?order_id=' . $result['order_id']);
                }
                exit;
            } else {
                $errors[] = $result['error'];
            }
        } else {
            // Обычное оформление заказа (наличными/картой курьеру)
            $order_data = [
                'delivery_address' => $delivery_address,
                'delivery_date' => $delivery_date,
                'delivery_time' => $delivery_time,
                'payment_method' => $payment_method,
                'bonus_used' => $use_bonuses,
                'discount_amount' => 0,
                'notes' => $comment
            ];
            
            $result = createOrder($_SESSION['user_id'], $order_data);
            
            if ($result['success']) {
                setFlash('success', 'Заказ успешно оформлен! Номер заказа: ' . $result['order_number']);
                redirect('/customer/orders.php');
                exit;
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$max_bonus_use = min($loyalty_card['points_balance'], $cart_total * 0.5);

// ТОЛЬКО ПОСЛЕ ОБРАБОТКИ POST ПОДКЛЮЧАЕМ HEADER
$page_title = 'Оформление заказа - Райский уголок';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.checkout-page {
    padding: 30px 0 60px;
}

.checkout-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.checkout-form {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.form-section {
    margin-bottom: 35px;
    padding-bottom: 35px;
    border-bottom: 2px solid var(--light-gray);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary-green);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 8px;
}

.form-label .required {
    color: var(--red-discount);
}

.form-input,
.form-textarea,
.form-select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    border-color: var(--primary-green);
    outline: none;
    box-shadow: 0 0 0 3px rgba(107, 191, 89, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

/* ПОЛЕ АДРЕСА С ПОИСКОМ */
.address-input-wrapper {
    position: relative;
}

.address-input-wrapper .form-textarea {
    padding-left: 45px;
}

.address-search-icon {
    position: absolute;
    left: 16px;
    top: 16px;
    color: var(--gray-text);
    font-size: 16px;
    pointer-events: none;
}

.address-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid var(--light-gray);
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 100;
    display: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.address-suggestions.active {
    display: block;
}

.address-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid var(--light-gray);
}

.address-suggestion-item:last-child {
    border-bottom: none;
}

.address-suggestion-item:hover {
    background: var(--light-green-bg);
}

.address-suggestion-title {
    font-weight: 600;
    color: var(--dark-text);
    font-size: 14px;
    margin-bottom: 4px;
}

.address-suggestion-desc {
    font-size: 13px;
    color: var(--gray-text);
}

/* КАРТА В ОБЫЧНОМ РЕЖИМЕ */
.map-container {
    position: relative;
    width: 100%;
    height: 300px;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 15px;
    border: 2px solid var(--light-gray);
}

#map {
    width: 100%;
    height: 100%;
}

/* ПОЛНОЭКРАННЫЙ РЕЖИМ КАРТЫ */
.map-fullscreen-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    animation: fadeIn 0.3s;
}

.map-fullscreen-overlay.active {
    display: block;
}

.map-fullscreen-container {
    position: relative;
    width: 100%;
    height: 100%;
}

#mapFullscreen {
    width: 100%;
    height: 100%;
}

.map-fullscreen-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    padding: 20px 30px;
    z-index: 10001;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.map-fullscreen-title {
    color: white;
    font-size: 24px;
    font-weight: 700;
}

.map-close-btn {
    width: 50px;
    height: 50px;
    background: white;
    border: none;
    border-radius: 50%;
    font-size: 24px;
    color: var(--dark-text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.map-close-btn:hover {
    background: var(--red-discount);
    color: white;
    transform: scale(1.1);
}

.map-fullscreen-controls {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 10001;
}

.map-fullscreen-btn {
    padding: 16px 30px;
    background: white;
    color: var(--dark-text);
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 10px;
}

.map-fullscreen-btn:hover {
    background: var(--primary-green);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

.map-fullscreen-btn i {
    font-size: 18px;
}

.map-confirm-btn {
    background: var(--primary-green);
    color: white;
}

.map-confirm-btn:hover {
    background: #5BAE49;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.map-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.map-btn {
    padding: 12px 20px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.map-btn:hover {
    background: #5BAE49;
}

.map-btn.secondary {
    background: white;
    color: var(--primary-green);
    border: 2px solid var(--primary-green);
}

.map-btn.secondary:hover {
    background: var(--light-green-bg);
}

.map-btn i {
    font-size: 16px;
}

.address-hint {
    font-size: 13px;
    color: var(--gray-text);
    margin-top: 8px;
}

.delivery-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.payment-option {
    padding: 20px;
    border: 2px solid var(--light-gray);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 15px;
}

.payment-option:hover {
    border-color: var(--primary-green);
    background: var(--light-green-bg);
}

.payment-option.selected {
    border-color: var(--primary-green);
    background: var(--light-green-bg);
}

.payment-option input {
    width: 20px;
    height: 20px;
}

.option-content {
    flex: 1;
}

.option-title {
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 4px;
}

.option-description {
    font-size: 13px;
    color: var(--gray-text);
}

.bonus-section {
    background: var(--light-green-bg);
    padding: 20px;
    border-radius: 12px;
}

.bonus-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.bonus-available {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-green);
}

.bonus-slider {
    margin-bottom: 10px;
}

.bonus-slider input[type="range"] {
    width: 100%;
    height: 8px;
    border-radius: 4px;
    background: #ddd;
    outline: none;
    -webkit-appearance: none;
}

.bonus-slider input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-green);
    cursor: pointer;
}

.bonus-slider input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-green);
    cursor: pointer;
    border: none;
}

.bonus-value {
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-text);
}

.order-summary {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    position: sticky;
    top: 90px;
    height: fit-content;
}

.summary-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 20px;
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding-right: 10px;
}

.order-item {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--light-gray);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #F5F5F5;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-quantity {
    font-size: 13px;
    color: var(--gray-text);
}

.item-price {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-green);
    white-space: nowrap;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 15px;
}

.summary-row.total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--light-gray);
    font-size: 20px;
    font-weight: 700;
}

.summary-label {
    color: var(--gray-text);
}

.summary-value {
    font-weight: 600;
    color: var(--dark-text);
}

.summary-value.green {
    color: var(--primary-green);
}

.summary-value.red {
    color: var(--red-discount);
}

.submit-order-btn {
    width: 100%;
    padding: 18px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 20px;
}

.submit-order-btn:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

.error-box {
    background: #FFF3F3;
    border: 1px solid #FFD6D6;
    color: var(--red-discount);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.error-box ul {
    margin: 10px 0 0 20px;
}

@media (max-width: 1024px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .checkout-page {
        padding: 20px 0 40px;
    }
    
    .checkout-form {
        padding: 20px;
    }
    
    .delivery-options {
        grid-template-columns: 1fr;
    }
    
    .map-controls {
        flex-direction: column;
    }
    
    .map-fullscreen-controls {
        flex-direction: column;
        bottom: 20px;
    }
}
</style>

<!-- Подключение Яндекс.Карт API -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=b0f8c858-eae3-43f9-a87f-130d39affd13&lang=ru_RU" type="text/javascript"></script>

<div class="checkout-page">
    <div class="container">
        <h1 style="font-size: 32px; font-weight: 700; color: var(--dark-green); margin-bottom: 30px;">
            Оформление заказа
        </h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
        <div class="error-box">
            <strong>Ошибки при оформлении заказа:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <form method="POST" class="checkout-form" id="checkoutForm">
                <!-- Контактные данные -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        Контактные данные
                    </h2>
                    <div class="form-group">
                        <label class="form-label">Имя и фамилия</label>
                        <input type="text" 
                               class="form-input" 
                               value="<?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>" 
                               readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" 
                               class="form-input" 
                               value="<?php echo e($user['email']); ?>" 
                               readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" 
                               class="form-input" 
                               value="<?php echo e($user['phone'] ?? ''); ?>" 
                               readonly>
                    </div>
                </div>
                
                <!-- Адрес доставки с картой -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Адрес доставки
                    </h2>
                    
                    <!-- Кнопки управления картой -->
                    <div class="map-controls">
                        <button type="button" class="map-btn" onclick="openFullscreenMap()">
                            <i class="fas fa-expand"></i>
                            Выбрать на карте
                        </button>
                        <button type="button" class="map-btn secondary" onclick="detectLocation()">
                            <i class="fas fa-crosshairs"></i>
                            Моё местоположение
                        </button>
                    </div>
                    
                    <!-- Маленькая превью карты -->
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Адрес <span class="required">*</span>
                        </label>
                        <div class="address-input-wrapper">
                            <i class="fas fa-search address-search-icon"></i>
                            <textarea name="delivery_address" 
                                      id="deliveryAddress"
                                      class="form-textarea" 
                                      placeholder="Начните вводить адрес для поиска или используйте карту" 
                                      required><?php echo isset($_POST['delivery_address']) ? e($_POST['delivery_address']) : ''; ?></textarea>
                            <div class="address-suggestions" id="addressSuggestions"></div>
                        </div>
                        <div class="address-hint">
                            <i class="fas fa-info-circle"></i> Начните вводить адрес для поиска или откройте карту на весь экран
                        </div>
                    </div>
                    
                    <div class="delivery-options">
                        <div class="form-group">
                            <label class="form-label">Дата доставки</label>
                            <input type="date" 
                                   name="delivery_date" 
                                   class="form-input"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Время доставки</label>
                            <select name="delivery_time" class="form-select">
                                <option value="9:00-12:00">9:00 - 12:00</option>
                                <option value="12:00-15:00">12:00 - 15:00</option>
                                <option value="15:00-18:00">15:00 - 18:00</option>
                                <option value="18:00-21:00">18:00 - 21:00</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Способ оплаты -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Способ оплаты
                    </h2>
                    <div class="payment-option selected">
                        <input type="radio" 
                               name="payment_method" 
                               value="cash" 
                               checked>
                        <div class="option-content">
                            <div class="option-title">Наличными курьеру</div>
                            <div class="option-description">Оплата при получении</div>
                        </div>
                    </div>
                    <div class="payment-option" style="margin-top: 10px;">
                        <input type="radio" 
                               name="payment_method" 
                               value="card">
                        <div class="option-content">
                            <div class="option-title">Картой курьеру</div>
                            <div class="option-description">Оплата при получении</div>
                        </div>
                    </div>
                    <div class="payment-option" style="margin-top: 10px;">
                        <input type="radio" 
                               name="payment_method" 
                               value="online">
                        <div class="option-content">
                            <div class="option-title">Онлайн оплата</div>
                            <div class="option-description">Оплата на сайте</div>
                        </div>
                    </div>
                </div>
                
                <!-- Использование бонусов -->
                <?php if ($loyalty_card && $loyalty_card['points_balance'] > 0): ?>
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-coins"></i>
                        Использовать бонусы
                    </h2>
                    <div class="bonus-section">
                        <div class="bonus-info">
                            <span>Доступно бонусов:</span>
                            <span class="bonus-available">
                                <?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?>
                            </span>
                        </div>
                        <div class="bonus-slider">
                            <input type="range" 
                                name="use_bonuses" 
                                min="0" 
                                max="<?php echo $max_bonus_use; ?>" 
                                value="0"
                                oninput="updateBonusValue(this.value)">
                        </div>
                        <div class="bonus-value">
                            Списать: <span id="bonusValue">0</span> бонусов
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Комментарий -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-comment"></i>
                        Комментарий к заказу
                    </h2>
                    <textarea name="comment" 
                              class="form-textarea" 
                              placeholder="Дополнительная информация для курьера"></textarea>
                </div>
            </form>
            
            <!-- Сводка заказа -->
            <div class="order-summary">
                <h3 class="summary-title">Ваш заказ</h3>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <?php if ($item['image_url']): ?>
                            <img src="<?php echo SITE_URL . '/assets/images/products/' . e($item['image_url']); ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <div class="item-name"><?php echo e($item['name']); ?></div>
                            <div class="item-quantity"><?php echo $item['quantity']; ?> шт × <?php echo formatPrice($item['price']); ?></div>
                        </div>
                        <div class="item-price"><?php echo formatPrice($item['total']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Товары (<?php echo count($cart_items); ?>):</span>
                    <span class="summary-value"><?php echo formatPrice($cart_total); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Доставка:</span>
                    <span class="summary-value green">Бесплатно</span>
                </div>
                
                <div class="summary-row" id="bonusRow" style="display: none;">
                    <span class="summary-label">Списано бонусов:</span>
                    <span class="summary-value red">-<span id="bonusAmount">0</span> ₽</span>
                </div>
                
                <div class="summary-row total">
                    <span class="summary-label">Итого:</span>
                    <span class="summary-value" id="totalAmount"><?php echo formatPrice($cart_total); ?></span>
                </div>
                
                <button type="submit" form="checkoutForm" class="submit-order-btn">
                    Оформить заказ
                </button>
                
                <div style="text-align: center; margin-top: 15px; font-size: 13px; color: var(--gray-text);">
                    Нажимая кнопку, вы соглашаетесь с условиями использования
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ПОЛНОЭКРАННАЯ КАРТА -->
<div class="map-fullscreen-overlay" id="mapFullscreenOverlay">
    <div class="map-fullscreen-header">
        <div class="map-fullscreen-title">Выберите точку доставки</div>
        <button class="map-close-btn" onclick="closeFullscreenMap()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="map-fullscreen-container">
        <div id="mapFullscreen"></div>
    </div>
    <div class="map-fullscreen-controls">
        <button class="map-fullscreen-btn" onclick="detectLocationFullscreen()">
            <i class="fas fa-crosshairs"></i>
            Определить моё местоположение
        </button>
        <button class="map-fullscreen-btn map-confirm-btn" onclick="confirmAddress()">
            <i class="fas fa-check"></i>
            Подтвердить адрес
        </button>
    </div>
</div>

<script>
const cartTotal = <?php echo $cart_total; ?>;
let myMap;
let myMapFullscreen;
let myPlacemark;
let myPlacemarkFullscreen;
let selectedCoords = null;
let selectedAddress = '';
let searchTimeout;

// Инициализация карт
ymaps.ready(init);

function init() {
    // Маленькая превью карта
    myMap = new ymaps.Map('map', {
        center: [55.75, 37.62],
        zoom: 10,
        controls: ['zoomControl']
    });
    
    // Полноэкранная карта
    myMapFullscreen = new ymaps.Map('mapFullscreen', {
        center: [55.75, 37.62],
        zoom: 12,
        controls: ['zoomControl', 'searchControl', 'geolocationControl']
    });
    
    // Клик по полноэкранной карте
    myMapFullscreen.events.add('click', function (e) {
        const coords = e.get('coords');
        setPlacemarkFullscreen(coords);
    });
    
    // Инициализация поиска в текстовом поле
    initAddressSearch();
}

// ПОИСК АДРЕСОВ В ТЕКСТОВОМ ПОЛЕ
function initAddressSearch() {
    const addressInput = document.getElementById('deliveryAddress');
    const suggestionsContainer = document.getElementById('addressSuggestions');
    
    addressInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 3) {
            suggestionsContainer.classList.remove('active');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchAddress(query);
        }, 500);
    });
    
    // Закрытие списка при клике вне
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.address-input-wrapper')) {
            suggestionsContainer.classList.remove('active');
        }
    });
}

function searchAddress(query) {
    const suggestionsContainer = document.getElementById('addressSuggestions');
    
    ymaps.geocode(query, {
        results: 10
    }).then(function (res) {
        suggestionsContainer.innerHTML = '';
        
        const geoObjects = res.geoObjects;
        
        if (geoObjects.getLength() === 0) {
            suggestionsContainer.innerHTML = '<div class="address-suggestion-item" style="color: #999; text-align: center;">Ничего не найдено</div>';
            suggestionsContainer.classList.add('active');
            return;
        }
        
        geoObjects.each(function (obj) {
            const address = obj.getAddressLine();
            const name = obj.properties.get('name');
            const description = obj.properties.get('description');
            const coords = obj.geometry.getCoordinates();
            
            const item = document.createElement('div');
            item.className = 'address-suggestion-item';
            item.innerHTML = `
                <div class="address-suggestion-title">${name || address}</div>
                ${description ? `<div class="address-suggestion-desc">${description}</div>` : ''}
            `;
            
            item.addEventListener('click', function() {
                selectAddressFromSuggestion(address, coords);
            });
            
            suggestionsContainer.appendChild(item);
        });
        
        suggestionsContainer.classList.add('active');
    });
}

function selectAddressFromSuggestion(address, coords) {
    selectedAddress = address;
    selectedCoords = coords;
    
    document.getElementById('deliveryAddress').value = address;
    document.getElementById('addressSuggestions').classList.remove('active');
    
    // Обновить маленькую карту
    if (myPlacemark) {
        myPlacemark.geometry.setCoordinates(coords);
    } else {
        myPlacemark = new ymaps.Placemark(coords, {
            iconCaption: 'Адрес доставки'
        }, {
            preset: 'islands#greenDotIcon'
        });
        myMap.geoObjects.add(myPlacemark);
    }
    
    myMap.setCenter(coords, 15);
    
    // Обновить полноэкранную карту
    if (myPlacemarkFullscreen) {
        myPlacemarkFullscreen.geometry.setCoordinates(coords);
        myPlacemarkFullscreen.properties.set('iconCaption', address);
    }
    myMapFullscreen.setCenter(coords, 15);
}

// ПОЛНОЭКРАННАЯ КАРТА
function openFullscreenMap() {
    document.getElementById('mapFullscreenOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (selectedCoords) {
        myMapFullscreen.setCenter(selectedCoords, 15);
    }
    
    setTimeout(() => {
        myMapFullscreen.container.fitToViewport();
    }, 100);
}

function closeFullscreenMap() {
    document.getElementById('mapFullscreenOverlay').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function setPlacemarkFullscreen(coords) {
    if (myPlacemarkFullscreen) {
        myPlacemarkFullscreen.geometry.setCoordinates(coords);
    } else {
        myPlacemarkFullscreen = new ymaps.Placemark(coords, {
            iconCaption: 'Адрес доставки'
        }, {
            preset: 'islands#greenDotIconWithCaption',
            draggable: true
        });
        myMapFullscreen.geoObjects.add(myPlacemarkFullscreen);
        
        myPlacemarkFullscreen.events.add('dragend', function () {
            const coords = myPlacemarkFullscreen.geometry.getCoordinates();
            getAddressFullscreen(coords);
        });
    }
    
    selectedCoords = coords;
    getAddressFullscreen(coords);
    myMapFullscreen.setCenter(coords, 15);
}

function getAddressFullscreen(coords) {
    ymaps.geocode(coords).then(function (res) {
        const firstGeoObject = res.geoObjects.get(0);
        selectedAddress = firstGeoObject.getAddressLine();
        
        myPlacemarkFullscreen.properties.set('iconCaption', selectedAddress);
    });
}

function detectLocationFullscreen() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const coords = [position.coords.latitude, position.coords.longitude];
                setPlacemarkFullscreen(coords);
            },
            function(error) {
                alert('Не удалось определить местоположение. Пожалуйста, разрешите доступ к геолокации.');
            }
        );
    } else {
        alert('Ваш браузер не поддерживает геолокацию');
    }
}

function confirmAddress() {
    if (!selectedAddress || !selectedCoords) {
        alert('Пожалуйста, выберите точку на карте');
        return;
    }
    
    document.getElementById('deliveryAddress').value = selectedAddress;
    
    // Обновить маленькую карту
    if (myPlacemark) {
        myPlacemark.geometry.setCoordinates(selectedCoords);
    } else {
        myPlacemark = new ymaps.Placemark(selectedCoords, {
            iconCaption: 'Адрес доставки'
        }, {
            preset: 'islands#greenDotIcon'
        });
        myMap.geoObjects.add(myPlacemark);
    }
    
    myMap.setCenter(selectedCoords, 15);
    
    closeFullscreenMap();
}

function detectLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const coords = [position.coords.latitude, position.coords.longitude];
                
                ymaps.geocode(coords).then(function (res) {
                    const firstGeoObject = res.geoObjects.get(0);
                    const address = firstGeoObject.getAddressLine();
                    
                    selectedAddress = address;
                    selectedCoords = coords;
                    
                    document.getElementById('deliveryAddress').value = address;
                    
                    // Обновить маленькую карту
                    if (myPlacemark) {
                        myPlacemark.geometry.setCoordinates(coords);
                    } else {
                        myPlacemark = new ymaps.Placemark(coords, {
                            iconCaption: 'Адрес доставки'
                        }, {
                            preset: 'islands#greenDotIcon'
                        });
                        myMap.geoObjects.add(myPlacemark);
                    }
                    
                    myMap.setCenter(coords, 15);
                    
                    // Обновить полноэкранную карту
                    if (myPlacemarkFullscreen) {
                        myPlacemarkFullscreen.geometry.setCoordinates(coords);
                        myPlacemarkFullscreen.properties.set('iconCaption', address);
                    }
                    myMapFullscreen.setCenter(coords, 15);
                });
            },
            function(error) {
                alert('Не удалось определить местоположение. Пожалуйста, разрешите доступ к геолокации.');
            }
        );
    } else {
        alert('Ваш браузер не поддерживает геолокацию');
    }
}

// БОНУСЫ - ПЛАВНОЕ ДВИЖЕНИЕ БЕЗ РЫВКОВ
function updateBonusValue(value) {
    const roundedValue = Math.round(value);
    
    document.getElementById('bonusValue').textContent = roundedValue;
    document.getElementById('bonusAmount').textContent = roundedValue;
    
    const bonusRow = document.getElementById('bonusRow');
    if (roundedValue > 0) {
        bonusRow.style.display = 'flex';
    } else {
        bonusRow.style.display = 'none';
    }
    
    const newTotal = cartTotal - parseFloat(roundedValue);
    document.getElementById('totalAmount').textContent = newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
}

// СПОСОБ ОПЛАТЫ
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// ЗАКРЫТИЕ ПО ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullscreenMap();
        document.getElementById('addressSuggestions').classList.remove('active');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>