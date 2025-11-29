<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА КОРЗИНЫ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Корзина - Райский уголок';
require_once __DIR__ . '/includes/header.php';

// Проверка авторизации
if (!$is_logged_in) {
    setFlash('info', 'Для просмотра корзины необходимо войти в систему');
    redirect('/login.php?redirect=/cart.php');
}

// Получить корзину
$cart_items = getCart($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);
$loyalty_card = getLoyaltyCard($_SESSION['user_id']);
?>

<style>
.cart-page {
    padding: 40px 0;
}

.cart-empty {
    text-align: center;
    padding: 80px 20px;
}

.cart-empty-icon {
    font-size: 80px;
    color: #ccc;
    margin-bottom: 20px;
}

.cart-empty h2 {
    font-size: 28px;
    color: var(--dark-text);
    margin-bottom: 15px;
}

.cart-empty p {
    color: var(--gray-text);
    margin-bottom: 30px;
    font-size: 16px;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    margin-bottom: 40px;
}

.cart-items {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--light-gray);
}

.cart-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-green);
}

.cart-count {
    background: var(--light-green-bg);
    color: var(--primary-green);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.cart-item {
    display: flex;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid var(--light-gray);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #F5F5F5;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.cart-item-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 8px;
}

.cart-item-name:hover {
    color: var(--primary-green);
}

.cart-item-weight {
    font-size: 14px;
    color: var(--gray-text);
    margin-bottom: 15px;
}

.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: auto;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border: 2px solid var(--primary-green);
    background: white;
    color: var(--primary-green);
    border-radius: 6px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    background: var(--primary-green);
    color: white;
}

.quantity-value {
    font-size: 16px;
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

.cart-item-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
}

.item-price {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-green);
}

.item-old-price {
    font-size: 14px;
    color: var(--gray-text);
    text-decoration: line-through;
}

.remove-item-btn {
    background: none;
    border: none;
    color: var(--red-discount);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    padding: 5px 10px;
    transition: all 0.3s;
}

.remove-item-btn:hover {
    color: #c41e24;
    text-decoration: underline;
}

/* Сводка заказа */
.cart-summary {
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
    margin-bottom: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 15px;
}

.summary-row.total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--light-gray);
    font-size: 18px;
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

.bonus-info {
    background: var(--light-green-bg);
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.bonus-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    margin-bottom: 8px;
}

.bonus-info-row:last-child {
    margin-bottom: 0;
}

.bonus-available {
    font-weight: 600;
    color: var(--primary-green);
}

.checkout-btn {
    width: 100%;
    padding: 18px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 20px;
}

.checkout-btn:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

.continue-shopping {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: var(--primary-green);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.continue-shopping:hover {
    text-decoration: underline;
}

/* Адаптивность */
@media (max-width: 1024px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-page {
        padding: 20px 0;
    }
    
    .cart-items {
        padding: 20px 15px;
    }
    
    .cart-header h1 {
        font-size: 24px;
    }
    
    .cart-item {
        flex-direction: column;
        gap: 15px;
    }
    
    .cart-item-image {
        width: 100%;
        height: 200px;
    }
    
    .cart-item-price {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    
    .cart-summary {
        padding: 20px 15px;
    }
}
</style>

<div class="cart-page">
    <div class="container">
        <?php if (empty($cart_items)): ?>
        <!-- Пустая корзина -->
        <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <h2>Ваша корзина пуста</h2>
            <p>Добавьте товары из каталога, чтобы начать оформление заказа</p>
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn-login" style="padding: 16px 40px;">
                Перейти в каталог
            </a>
        </div>
        
        <?php else: ?>
        <!-- Корзина с товарами -->
        <div class="cart-content">
            <div class="cart-items">
                <div class="cart-header">
                    <h1>Корзина</h1>
                    <span class="cart-count"><?php echo count($cart_items); ?> товаров</span>
                </div>
                
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                    <div class="cart-item-image">
                        <?php if ($item['image_url']): ?>
                        <img src="<?php echo SITE_URL . '/assets/images/products/' . e($item['image_url']); ?>" 
                             alt="<?php echo e($item['name']); ?>">
                        <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="Нет изображения">
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item-details">
                        <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>" 
                           class="cart-item-name">
                            <?php echo e($item['name']); ?>
                        </a>
                        <div class="cart-item-weight">
                            <?php echo e($item['weight']); ?> <?php echo e($item['unit']); ?>
                        </div>
                        
                        <div class="cart-item-controls">
                            <div class="quantity-control">
                                <button class="quantity-btn" 
                                        onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                    −
                                </button>
                                <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" 
                                        onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                    +
                                </button>
                            </div>
                            
                            <button class="remove-item-btn" 
                                    onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                    
                    <div class="cart-item-price">
                        <div>
                            <?php if ($item['old_price']): ?>
                            <div class="item-old-price"><?php echo formatPrice($item['old_price'] * $item['quantity']); ?></div>
                            <?php endif; ?>
                            <div class="item-price"><?php echo formatPrice($item['total']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Сводка заказа -->
            <div class="cart-summary">
                <h3 class="summary-title">Итого</h3>
                
                <div class="summary-row">
                    <span class="summary-label">Товаров:</span>
                    <span class="summary-value"><?php echo count($cart_items); ?> шт</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Сумма:</span>
                    <span class="summary-value"><?php echo formatPrice($cart_total); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Доставка:</span>
                    <span class="summary-value green">Бесплатно</span>
                </div>
                
                <?php if ($loyalty_card): ?>
                <div class="bonus-info">
                    <div class="bonus-info-row">
                        <span>Доступно бонусов:</span>
                        <span class="bonus-available">
                            <?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?>
                        </span>
                    </div>
                    <div class="bonus-info-row">
                        <span>Вы получите:</span>
                        <span class="bonus-available">
                            +<?php echo number_format(calculateCashback($_SESSION['user_id'], $cart_total), 0, '.', ' '); ?> бонусов
                        </span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="summary-row total">
                    <span class="summary-label">Итого:</span>
                    <span class="summary-value"><?php echo formatPrice($cart_total); ?></span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/checkout.php" class="checkout-btn">
                    Оформить заказ
                </a>
                
                <a href="<?php echo SITE_URL; ?>/catalog.php" class="continue-shopping">
                    Продолжить покупки
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) {
        if (!confirm('Удалить товар из корзины?')) {
            return;
        }
        removeItem(productId);
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка при обновлении количества');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
}

function removeItem(productId) {
    if (!confirm('Удалить товар из корзины?')) {
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка при удалении товара');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>