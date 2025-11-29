<?php
/**
 * ═══════════════════════════════════════════════════════════
 * МОИ ЗАКАЗЫ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Мои заказы - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('customer');

$orders = getUserOrders($_SESSION['user_id']);
?>

<style>
.orders-page {
    padding: 30px 0 60px;
}

.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark-green);
}

.orders-count {
    background: var(--light-green-bg);
    color: var(--primary-green);
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.order-card:hover {
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.2);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--light-gray);
}

.order-number {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark-text);
}

.order-date {
    font-size: 13px;
    color: var(--gray-text);
    margin-top: 5px;
}

.order-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.order-status.pending {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.order-status.confirmed {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.order-status.processing {
    background: #E3F2FD;
    color: #2196F3;
}

.order-status.shipped {
    background: #F3E5F5;
    color: #9C27B0;
}

.order-status.delivered {
    background: #E8F5E9;
    color: #4CAF50;
}

.order-status.cancelled {
    background: #FFEBEE;
    color: #F44336;
}

.order-details {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 20px;
    align-items: center;
}

.order-address {
    font-size: 14px;
    color: var(--dark-text);
}

.order-address i {
    color: var(--primary-green);
    margin-right: 5px;
}

.order-info-item {
    text-align: center;
}

.info-label {
    font-size: 12px;
    color: var(--gray-text);
    margin-bottom: 5px;
}

.info-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-green);
}

.order-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.btn-view {
    padding: 10px 20px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-view:hover {
    background: #5BAE49;
}

.empty-orders {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-orders h3 {
    font-size: 24px;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.empty-orders p {
    color: var(--gray-text);
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .orders-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .order-details {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .order-info-item {
        text-align: left;
    }
    
    .order-card {
        padding: 20px;
    }
}
</style>

<div class="orders-page">
    <div class="container">
        <div class="orders-header">
            <h1 class="page-title">Мои заказы</h1>
            <?php if (!empty($orders)): ?>
            <span class="orders-count">Всего заказов: <?php echo count($orders); ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (empty($orders)): ?>
        <!-- Нет заказов -->
        <div class="empty-orders">
            <div class="empty-icon">📦</div>
            <h3>У вас пока нет заказов</h3>
            <p>Начните делать покупки прямо сейчас!</p>
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn-login" style="padding: 16px 40px;">
                Перейти в каталог
            </a>
        </div>
        
        <?php else: ?>
        <!-- Список заказов -->
        <div class="orders-list">
            <?php foreach ($orders as $order): 
                $order_items = getOrderItems($order['id']);
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">Заказ №<?php echo e($order['order_number']); ?></div>
                        <div class="order-date">
                            <?php echo formatDateTime($order['created_at'], 'd.m.Y в H:i'); ?>
                        </div>
                    </div>
                    <span class="order-status <?php echo $order['status']; ?>">
                        <?php echo getOrderStatusName($order['status']); ?>
                    </span>
                </div>
                
                <div class="order-details">
                    <div class="order-address">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo e($order['delivery_address']); ?>
                        <?php if ($order['delivery_date']): ?>
                        <br>
                        <small style="color: var(--gray-text);">
                            Доставка: <?php echo formatDate($order['delivery_date']); ?>
                            <?php if ($order['delivery_time']): ?>
                            , <?php echo e($order['delivery_time']); ?>
                            <?php endif; ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-info-item">
                        <div class="info-label">Товаров</div>
                        <div class="info-value"><?php echo count($order_items); ?> шт</div>
                    </div>
                    
                    <div class="order-info-item">
                        <div class="info-label">Сумма</div>
                        <div class="info-value"><?php echo formatPrice($order['final_amount']); ?></div>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="#" class="btn-view" onclick="viewOrder(<?php echo $order['id']; ?>); return false;">
                        <i class="fas fa-eye"></i> Подробнее
                    </a>
                    <?php if ($order['status'] === 'pending'): ?>
                    <button class="btn-view" style="background: var(--red-discount);" 
                            onclick="cancelOrder(<?php echo $order['id']; ?>)">
                        <i class="fas fa-times"></i> Отменить
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewOrder(orderId) {
    alert('Просмотр заказа #' + orderId + '\nФункционал в разработке');
}

function cancelOrder(orderId) {
    if (confirm('Вы уверены, что хотите отменить заказ?')) {
        alert('Отмена заказа #' + orderId + '\nФункционал в разработке');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>