<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ПАНЕЛЬ ДИРЕКТОРА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Панель директора - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('director');

$user = getCurrentUser();

// Финансовая статистика
$stmt = $pdo->query("
    SELECT 
        SUM(final_amount) as total_revenue,
        COUNT(*) as total_orders
    FROM orders 
    WHERE status != 'cancelled'
");
$finance = $stmt->fetch();

$stmt = $pdo->query("
    SELECT SUM(final_amount) as month_revenue
    FROM orders 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
    AND status != 'cancelled'
");
$month_revenue = $stmt->fetch()['month_revenue'] ?? 0;

$stmt = $pdo->query("
    SELECT SUM(final_amount) as today_revenue
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
    AND status != 'cancelled'
");
$today_revenue = $stmt->fetch()['today_revenue'] ?? 0;

// Статистика пользователей
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'");
$total_customers = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role != 'customer'");
$total_staff = $stmt->fetch()['count'];

// Последние заказы
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();
?>

<style>
.director-dashboard {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.director-header {
    background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
}

.director-header h1 {
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

.stat-icon.blue { background: #E3F2FD; color: #2196F3; }
.stat-icon.green { background: var(--light-green-bg); color: var(--primary-green); }
.stat-icon.orange { background: #FFF3E6; color: var(--accent-orange); }
.stat-icon.purple { background: #F3E5F5; color: #9C27B0; }

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-text);
}

.section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
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
    border-color: #1a237e;
    transform: translateY(-2px);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: #E3F2FD;
    color: #2196F3;
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
</style>

<div class="director-dashboard">
    <div class="container">
        <div class="director-header">
            <h1>📊 Панель директора</h1>
            <p>Добро пожаловать, <?php echo e($user['first_name']); ?>! Управление магазином "Райский уголок"</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-ruble-sign"></i>
                </div>
                <div class="stat-value"><?php echo formatPrice($finance['total_revenue'] ?? 0); ?></div>
                <div class="stat-label">Общая выручка</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-value"><?php echo formatPrice($today_revenue); ?></div>
                <div class="stat-label">Выручка за сегодня</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?php echo formatPrice($month_revenue); ?></div>
                <div class="stat-label">Выручка за месяц</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $total_customers; ?></div>
                <div class="stat-label">Активных клиентов</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/director/analytics.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="action-text">
                    <h3>Аналитика</h3>
                    <p>Отчёты и графики</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/director/staff.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="action-text">
                    <h3>Сотрудники</h3>
                    <p>Управление персоналом</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/director/quotes.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="action-text">
                    <h3>Котировки</h3>
                    <p>Запросы поставщикам</p>
                </div>
            </a>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Последние заказы</h2>
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Клиент</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong><?php echo e($order['order_number']); ?></strong></td>
                        <td><?php echo e($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><strong><?php echo formatPrice($order['final_amount']); ?></strong></td>
                        <td><?php echo getOrderStatusName($order['status']); ?></td>
                        <td><?php echo formatDateTime($order['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>