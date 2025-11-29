<?php
$page_title = 'Заказ оформлен - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');

$order_id = $_GET['order_id'] ?? null;

if ($order_id) {
    $order = getOrderById($order_id, $_SESSION['user_id']);
}

// Очистить сессию pending_order
unset($_SESSION['pending_order']);
unset($_SESSION['payment_id']);
?>

<style>
.success-page {
    padding: 60px 0;
    text-align: center;
    min-height: 60vh;
}

.success-icon {
    font-size: 100px;
    color: var(--primary-green);
    margin-bottom: 30px;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

.success-title {
    font-size: 36px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 15px;
}

.success-message {
    font-size: 18px;
    color: var(--gray-text);
    margin-bottom: 40px;
}

.order-info-box {
    display: inline-block;
    background: var(--light-green-bg);
    padding: 30px 50px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.order-number {
    font-size: 14px;
    color: var(--gray-text);
    margin-bottom: 10px;
}

.order-number strong {
    font-size: 24px;
    color: var(--primary-green);
    display: block;
    margin-top: 5px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 16px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-primary:hover {
    background: #5BAE49;
    transform: translateY(-2px);
}

.btn-secondary {
    background: white;
    color: var(--primary-green);
    border: 2px solid var(--primary-green);
}

.btn-secondary:hover {
    background: var(--light-green-bg);
}
</style>

<div class="success-page">
    <div class="container">
        <div class="success-icon">✓</div>
        
        <h1 class="success-title">Заказ успешно оплачен!</h1>
        
        <p class="success-message">
            Спасибо за покупку! Мы начали обрабатывать ваш заказ.<br>
            Вы получите уведомление о статусе заказа на email.
        </p>
        
        <?php if (isset($order)): ?>
        <div class="order-info-box">
            <div class="order-number">
                Номер заказа
                <strong><?php echo e($order['order_number']); ?></strong>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="<?php echo SITE_URL; ?>/customer/orders.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Мои заказы
            </a>
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn btn-secondary">
                <i class="fas fa-shopping-basket"></i> Продолжить покупки
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>