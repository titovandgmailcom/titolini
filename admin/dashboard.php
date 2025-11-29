<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ПАНЕЛЬ АДМИНИСТРАТОРА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Панель администратора - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('admin');

$user = getCurrentUser();

// Статистика
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$total_users = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$total_products = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE is_active = 1");
$total_categories = $stmt->fetch()['count'];

// Последние заказы
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Новые пользователи
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE role = 'customer' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$new_users = $stmt->fetchAll();
?>

<style>
.admin-dashboard {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.admin-header {
    background: linear-gradient(135deg, #2D5016 0%, #3D6B1F 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
}

.admin-header h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
}

.admin-header p {
    font-size: 16px;
    opacity: 0.9;
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
    transition: all 0.3s;
}

.stat-card:hover {
    box-shadow: 0 6px 20px rgba(45, 80, 22, 0.15);
    transform: translateY(-4px);
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

.stat-icon.green {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.stat-icon.blue {
    background: #E3F2FD;
    color: #2196F3;
}

.stat-icon.orange {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.stat-icon.purple {
    background: #F3E5F5;
    color: #9C27B0;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-text);
    font-weight: 500;
}

.admin-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
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

.btn-action {
    padding: 10px 20px;
    background: var(--primary-green);
    color: white;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-action:hover {
    background: #5BAE49;
    transform: translateY(-2px);
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
    color: var(--dark-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.orders-table td {
    padding: 18px 15px;
    border-bottom: 1px solid #F0F0F0;
    font-size: 14px;
}

.orders-table tr:hover {
    background: #FAFAFA;
}

.order-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
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

.user-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 1px solid #F0F0F0;
    border-radius: 8px;
    margin-bottom: 12px;
    transition: all 0.3s;
}

.user-card:hover {
    border-color: var(--primary-green);
    background: var(--light-green-bg);
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.user-info h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 4px;
}

.user-info p {
    font-size: 13px;
    color: var(--gray-text);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    background: var(--light-green-bg);
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

.action-text {
    flex: 1;
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

@media (max-width: 768px) {
    .admin-header h1 {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        font-size: 12px;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 10px;
    }
}
</style>

<div class="admin-dashboard">
    <div class="container">
        <!-- Шапка -->
        <div class="admin-header">
            <h1>👋 Добро пожаловать, <?php echo e($user['first_name']); ?>!</h1>
            <p>Панель администратора интернет-магазина "Райский уголок"</p>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Активных пользователей</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo $total_products; ?></div>
                <div class="stat-label">Товаров в каталоге</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo $pending_orders; ?></div>
                <div class="stat-label">Заказов на обработке</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="stat-value"><?php echo $total_categories; ?></div>
                <div class="stat-label">Категорий товаров</div>
            </div>
        </div>
        
        <!-- Быстрые действия -->
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/admin/catalog.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="action-text">
                    <h3>Каталог</h3>
                    <p>Управление товарами</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/moderation.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="action-text">
                    <h3>Модерация</h3>
                    <p>Проверка контента</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/banners.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-image"></i>
                </div>
                <div class="action-text">
                    <h3>Баннеры</h3>
                    <p>Управление акциями</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/director/dashboard.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="action-text">
                    <h3>Аналитика</h3>
                    <p>Отчёты и статистика</p>
                </div>
            </a>
        </div>
        
        <!-- Последние заказы -->
        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Последние заказы</h2>
                <a href="<?php echo SITE_URL; ?>/director/dashboard.php" class="btn-action">
                    Все заказы
                </a>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <p style="color: #999; text-align: center; padding: 40px;">Заказов пока нет</p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Номер заказа</th>
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
                            <td>
                                <span class="order-status <?php echo e($order['status']); ?>">
                                    <?php echo getOrderStatusName($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDateTime($order['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Новые пользователи -->
        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Новые пользователи</h2>
            </div>
            
            <?php if (empty($new_users)): ?>
                <p style="color: #999; text-align: center; padding: 40px;">Новых пользователей нет</p>
            <?php else: ?>
                <?php foreach ($new_users as $new_user): ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($new_user['first_name'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h4><?php echo e($new_user['first_name'] . ' ' . $new_user['last_name']); ?></h4>
                        <p><?php echo e($new_user['email']); ?> • Зарегистрирован <?php echo formatDateTime($new_user['created_at']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>