<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ПАНЕЛЬ МЕНЕДЖЕРА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Панель менеджера - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('manager');

$user = getCurrentUser();

// Статистика обращений
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_requests = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active' AND role = 'customer'");
$total_customers = $stmt->fetch()['count'];

// Последние заказы для обработки
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.status IN ('pending', 'confirmed')
    ORDER BY o.created_at DESC 
    LIMIT 15
");
$orders = $stmt->fetchAll();
?>

<style>
.manager-dashboard {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.manager-header {
    background: linear-gradient(135deg, #9C27B0 0%, #AB47BC 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
}

.manager-header h1 {
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

.stat-icon.purple { background: #F3E5F5; color: #9C27B0; }
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
    border-color: #9C27B0;
    transform: translateY(-2px);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: #F3E5F5;
    color: #9C27B0;
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

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #F9F9F9;
    padding: 15px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
}

.orders-table td {
    padding: 18px 15px;
    border-bottom: 1px solid #F0F0F0;
    font-size: 14px;
}

.order-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.order-status.pending { background: #FFF3E6; color: var(--accent-orange); }
.order-status.confirmed { background: var(--light-green-bg); color: var(--primary-green); }

.btn-contact {
    padding: 8px 16px;
    background: #F3E5F5;
    color: #9C27B0;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.btn-contact:hover {
    background: #9C27B0;
    color: white;
}
</style>

<div class="manager-dashboard">
    <div class="container">
        <div class="manager-header">
            <h1>💬 Панель менеджера</h1>
            <p>Добро пожаловать, <?php echo e($user['first_name']); ?>! Управление клиентами и поддержкой</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $pending_requests; ?></div>
                <div class="stat-label">Ожидают обработки</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $total_customers; ?></div>
                <div class="stat-label">Активных клиентов</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Открытых чатов</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/manager/chats.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="action-text">
                    <h3>Чаты</h3>
                    <p>Общение с клиентами</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/manager/support.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="action-text">
                    <h3>Поддержка</h3>
                    <p>Обращения клиентов</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/manager/quotes.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="action-text">
                    <h3>Котировки</h3>
                    <p>Запросы цен</p>
                </div>
            </a>
        </div>
        
        <div class="section">
            <h2 class="section-title">Заказы на обработке</h2>
            
            <?php if (empty($orders)): ?>
                <p style="text-align: center; color: #999; padding: 40px;">Нет заказов на обработке</p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Номер заказа</th>
                            <th>Клиент</th>
                            <th>Телефон</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo e($order['order_number']); ?></strong></td>
                            <td><?php echo e($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo e($order['phone'] ?? 'Не указан'); ?></td>
                            <td><strong><?php echo formatPrice($order['final_amount']); ?></strong></td>
                            <td>
                                <span class="order-status <?php echo e($order['status']); ?>">
                                    <?php echo getOrderStatusName($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDateTime($order['created_at']); ?></td>
                            <td>
                                <a href="tel:<?php echo e($order['phone']); ?>" class="btn-contact">
                                    <i class="fas fa-phone"></i> Позвонить
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>