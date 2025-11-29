<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ЛИЧНЫЙ КАБИНЕТ ПОКУПАТЕЛЯ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Личный кабинет - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('customer');

/**
 * Генерация номера карты лояльности
 */
function generateLoyaltyCardNumber() {
    $parts = [];
    for ($i = 0; $i < 4; $i++) {
        $parts[] = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    return implode('-', $parts);
}

$user = getCurrentUser();

// Проверка существования пользователя в БД
if (!$user) {
    session_destroy();
    setFlash('error', 'Ваш аккаунт не найден. Пожалуйста, зарегистрируйтесь заново.');
    redirect('/register.php');
    exit;
}

$loyalty_card = getLoyaltyCard($_SESSION['user_id']);

// Если карта лояльности не найдена - создать её
if (!$loyalty_card) {
    try {
        $pdo->beginTransaction();
        
        $card_number = generateLoyaltyCardNumber();
        $stmt = $pdo->prepare("
            INSERT INTO loyalty_cards (user_id, card_number, current_level, points_balance)
            VALUES (?, ?, 'bronze', 100)
        ");
        $stmt->execute([$_SESSION['user_id'], $card_number]);
        
        // Начислить приветственный бонус
        $stmt = $pdo->prepare("
            INSERT INTO loyalty_transactions (user_id, type, amount, description)
            VALUES (?, 'bonus', 100, 'Приветственный бонус')
        ");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        $loyalty_card = getLoyaltyCard($_SESSION['user_id']);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Dashboard Loyalty Card Creation Error: " . $e->getMessage());
        
        // Если ошибка создания карты - выйти и попросить войти заново
        session_destroy();
        setFlash('error', 'Произошла ошибка. Пожалуйста, войдите заново.');
        redirect('/login.php');
        exit;
    }
}

// Если карта всё ещё не создана - выйти
if (!$loyalty_card) {
    session_destroy();
    setFlash('error', 'Ошибка инициализации аккаунта. Обратитесь в поддержку.');
    redirect('/login.php');
    exit;
}

$recent_orders = getUserOrders($_SESSION['user_id'], 5);
$favorites_count = getFavoritesCount($_SESSION['user_id']);

// Статистика
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetch()['count'];

$level_info = LOYALTY_LEVELS[$loyalty_card['current_level']];
?>

<style>
.dashboard-page {
    padding: 30px 0 60px;
}

.dashboard-header {
    background: linear-gradient(135deg, var(--primary-green) 0%, #5BAE49 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 30px;
    position: relative;
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: var(--primary-green);
    flex-shrink: 0;
}

.user-info {
    flex: 1;
}

.user-info h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
}

.user-level {
    display: inline-block;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.logout-btn {
    position: absolute;
    top: 30px;
    right: 30px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
}

.logout-btn i {
    font-size: 16px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.stat-card:hover {
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.2);
    transform: translateY(-4px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-icon.green {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.stat-icon.orange {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.stat-icon.blue {
    background: #E3F2FD;
    color: #2196F3;
}

.stat-icon.red {
    background: #FFE5E5;
    color: var(--red-discount);
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-text);
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 20px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 40px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px;
    background: white;
    border: 2px solid var(--light-gray);
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    color: var(--dark-text);
    text-decoration: none;
    transition: all 0.3s;
}

.action-btn:hover {
    border-color: var(--primary-green);
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.action-btn i {
    font-size: 20px;
}

.recent-orders {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid var(--light-gray);
}

.order-item:last-child {
    border-bottom: none;
}

.order-info {
    flex: 1;
}

.order-number {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.order-date {
    font-size: 13px;
    color: var(--gray-text);
}

.order-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin: 0 15px;
}

.order-status.pending {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.order-status.confirmed {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.order-status.delivered {
    background: #E8F5E9;
    color: #4CAF50;
}

.order-total {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-green);
}

.no-orders {
    text-align: center;
    padding: 40px 20px;
    color: var(--gray-text);
}

.btn-login {
    display: inline-block;
    padding: 14px 28px;
    background: var(--primary-green);
    color: white;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-login:hover {
    background: #5BAE49;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 191, 89, 0.3);
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
        padding-top: 70px;
    }
    
    .logout-btn {
        top: 20px;
        right: 20px;
        padding: 10px 16px;
        font-size: 13px;
    }
    
    .user-info h1 {
        font-size: 24px;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .order-status {
        margin: 0;
    }
}
</style>

<div class="dashboard-page">
    <div class="container">
        <!-- Шапка профиля -->
        <div class="dashboard-header">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <h1>Добро пожаловать, <?php echo e($user['first_name']); ?>!</h1>
                <span class="user-level">
                    <?php echo $level_info['name']; ?> • 
                    <?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?> бонусов
                </span>
            </div>
            <a href="<?php echo SITE_URL; ?>/logout.php" class="logout-btn" onclick="return confirm('Вы уверены, что хотите выйти?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Выход</span>
            </a>
        </div>
        
        <!-- Статистика -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-value"><?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?></div>
                <div class="stat-label">Доступно бонусов</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo $total_orders; ?></div>
                <div class="stat-label">Всего заказов</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-value"><?php echo $favorites_count; ?></div>
                <div class="stat-label">В избранном</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-percent"></i>
                </div>
                <div class="stat-value"><?php echo $level_info['cashback_percent']; ?>%</div>
                <div class="stat-label">Ваш кешбэк</div>
            </div>
        </div>
        
        <!-- Быстрые действия -->
        <h2 class="section-title">Быстрые действия</h2>
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="action-btn">
                <i class="fas fa-shopping-cart"></i>
                <span>Сделать заказ</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/customer/orders.php" class="action-btn">
                <i class="fas fa-box"></i>
                <span>Мои заказы</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/customer/loyalty.php" class="action-btn">
                <i class="fas fa-id-card"></i>
                <span>Карта лояльности</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/customer/profile.php" class="action-btn">
                <i class="fas fa-user-cog"></i>
                <span>Настройки</span>
            </a>
        </div>
        
        <!-- Последние заказы -->
        <h2 class="section-title">Последние заказы</h2>
        <div class="recent-orders">
            <?php if (empty($recent_orders)): ?>
            <div class="no-orders">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                <p>У вас пока нет заказов</p>
                <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn-login" style="margin-top: 20px;">
                    Перейти в каталог
                </a>
            </div>
            <?php else: ?>
                <?php foreach ($recent_orders as $order): ?>
                <div class="order-item">
                    <div class="order-info">
                        <div class="order-number">Заказ №<?php echo e($order['order_number']); ?></div>
                        <div class="order-date"><?php echo formatDateTime($order['created_at']); ?></div>
                    </div>
                    <span class="order-status <?php echo e($order['status']); ?>">
                        <?php echo getOrderStatusName($order['status']); ?>
                    </span>
                    <div class="order-total"><?php echo formatPrice($order['final_amount']); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="<?php echo SITE_URL; ?>/customer/orders.php" class="btn-login">
                        Все заказы
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>