<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ПАНЕЛЬ КУРЬЕРА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Панель курьера - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('courier');

$user = getCurrentUser();

// Статистика доставок
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE status = 'shipped'
");
$active_deliveries = $stmt->fetch()['count'];

$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE status = 'delivered' 
    AND DATE(updated_at) = CURDATE()
");
$today_deliveries = $stmt->fetch()['count'];

// Активные заказы на доставку
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'shipped'
    ORDER BY o.delivery_date, o.delivery_time
    LIMIT 20
");
$active_orders = $stmt->fetchAll();
?>

<style>
.courier-dashboard {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.courier-header {
    background: linear-gradient(135deg, #6BBF59 0%, #5BAE49 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
}

.courier-header h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 15px;
}

.stat-icon.green { background: var(--light-green-bg); color: var(--primary-green); }
.stat-icon.blue { background: #E3F2FD; color: #2196F3; }
.stat-icon.orange { background: #FFF3E6; color: var(--accent-orange); }

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-text);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 40px;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: white;
    border: 2px solid var(--light-gray);
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s;
}

.action-card:hover {
    border-color: var(--primary-green);
    transform: translateY(-2px);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: var(--light-green-bg);
    color: var(--primary-green);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.action-text h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 4px;
}

.action-text p {
    font-size: 13px;
    color: var(--gray-text);
}

.section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 25px;
}

.delivery-card {
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.delivery-card:hover {
    border-color: var(--primary-green);
    box-shadow: 0 4px 12px rgba(107, 191, 89, 0.15);
}

.delivery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.delivery-number {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark-text);
}

.delivery-time {
    font-size: 14px;
    color: var(--primary-green);
    font-weight: 600;
}

.delivery-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.info-item {
    font-size: 14px;
}

.info-label {
    color: var(--gray-text);
    margin-bottom: 4px;
}

.info-value {
    font-weight: 600;
    color: var(--dark-text);
}

.delivery-address {
    background: #F9F9F9;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.delivery-address strong {
    display: block;
    margin-bottom: 5px;
    color: var(--dark-text);
}

.delivery-actions {
    display: flex;
    gap: 10px;
}

.btn-route, .btn-call, .btn-complete {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
}

.btn-route {
    background: #E3F2FD;
    color: #2196F3;
}

.btn-call {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.btn-complete {
    background: var(--light-green-bg);
    color: var(--primary-green);
}
</style>

<div class="courier-dashboard">
    <div class="container">
        <div class="courier-header">
            <h1>🚚 Панель курьера</h1>
            <p>Добро пожаловать, <?php echo e($user['first_name']); ?>! Текущие доставки</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-value"><?php echo $active_deliveries; ?></div>
                <div class="stat-label">Активных доставок</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $today_deliveries; ?></div>
                <div class="stat-label">Доставлено сегодня</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-value">0 км</div>
                <div class="stat-label">Пройдено сегодня</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/courier/orders.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="action-text">
                    <h3>Заказы</h3>
                    <p>Список доставок</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/courier/navigation.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="action-text">
                    <h3>Навигация</h3>
                    <p>Маршруты доставки</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/courier/stats.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-text">
                    <h3>Статистика</h3>
                    <p>Мои показатели</p>
                </div>
            </a>
        </div>
        
        <div class="section">
            <h2 class="section-title">Активные доставки (<?php echo count($active_orders); ?>)</h2>
            
            <?php if (empty($active_orders)): ?>
                <p style="text-align: center; color: #999; padding: 40px;">Нет активных доставок</p>
            <?php else: ?>
                <?php foreach ($active_orders as $order): ?>
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div class="delivery-number">Заказ №<?php echo e($order['order_number']); ?></div>
                        <div class="delivery-time">
                            <?php if ($order['delivery_date']): ?>
                                📅 <?php echo date('d.m.Y', strtotime($order['delivery_date'])); ?>
                                <?php echo $order['delivery_time'] ? ' • ' . e($order['delivery_time']) : ''; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="delivery-info">
                        <div class="info-item">
                            <div class="info-label">Клиент</div>
                            <div class="info-value"><?php echo e($order['first_name'] . ' ' . $order['last_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Телефон</div>
                            <div class="info-value"><?php echo e($order['phone'] ?? 'Не указан'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Сумма заказа</div>
                            <div class="info-value"><?php echo formatPrice($order['final_amount']); ?></div>
                        </div>
                    </div>
                    
                    <div class="delivery-address">
                        <strong>📍 Адрес доставки:</strong>
                        <?php echo e($order['delivery_address']); ?>
                    </div>
                    
                    <div class="delivery-actions">
                        <a href="https://yandex.ru/maps/?text=<?php echo urlencode($order['delivery_address']); ?>" 
                           target="_blank" 
                           class="btn-route">
                            <i class="fas fa-map"></i> Маршрут
                        </a>
                        <a href="tel:<?php echo e($order['phone']); ?>" class="btn-call">
                            <i class="fas fa-phone"></i> Позвонить
                        </a>
                        <button class="btn-complete" onclick="alert('Функция в разработке')">
                            <i class="fas fa-check"></i> Доставлено
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>