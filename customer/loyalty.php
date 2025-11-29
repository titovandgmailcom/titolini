<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ПРОГРАММА ЛОЯЛЬНОСТИ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Программа лояльности - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('customer');

$user = getCurrentUser();
$loyalty_card = getLoyaltyCard($_SESSION['user_id']);
$level_info = LOYALTY_LEVELS[$loyalty_card['current_level']];

// История транзакций
$stmt = $pdo->prepare("
    SELECT * FROM loyalty_transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Прогресс до следующего уровня
$current_spent = $loyalty_card['total_spent'];
$next_level = null;
$progress_percent = 0;

foreach (LOYALTY_LEVELS as $level => $info) {
    if ($info['min_spent'] > $current_spent) {
        $next_level = $info;
        $remaining = $info['min_spent'] - $current_spent;
        $level_range = $info['min_spent'] - $level_info['min_spent'];
        
        // Защита от деления на ноль
        if ($level_range > 0) {
            $progress_percent = (($current_spent - $level_info['min_spent']) / $level_range) * 100;
        } else {
            $progress_percent = 100;
        }
        break;
    }
}

// Если достигнут максимальный уровень
if ($next_level === null) {
    $progress_percent = 100;
}
?>

<style>
.loyalty-page {
    padding: 30px 0 60px;
}

/* Карта лояльности - ПРЕМИУМ */
.loyalty-card-container {
    perspective: 1200px;
    margin: 30px auto 40px;
    max-width: 450px;
}

.loyalty-card {
    width: 100%;
    height: 280px;
    border-radius: 20px;
    padding: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25), 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.6s ease;
    transform-style: preserve-3d;
}

.loyalty-card:hover {
    transform: rotateY(5deg) rotateX(5deg) scale(1.02);
}

.loyalty-card.bronze {
    background: linear-gradient(135deg, #8B4513 0%, #CD853F 50%, #DAA520 100%);
}

.loyalty-card.silver {
    background: linear-gradient(135deg, #A8B8C8 0%, #C0C0C0 50%, #87CEEB 100%);
}

.loyalty-card.gold {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
}

.loyalty-card.platinum {
    background: linear-gradient(135deg, #E5E4E2 0%, #C9C0DE 50%, #9370DB 100%);
}

.loyalty-card::before {
    content: '';
    position: absolute;
    width: 350px;
    height: 350px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle cx="50" cy="50" r="40" fill="white" opacity="0.05"/><circle cx="150" cy="50" r="30" fill="white" opacity="0.05"/><circle cx="100" cy="150" r="45" fill="white" opacity="0.05"/></svg>');
    background-size: contain;
    top: -80px;
    right: -80px;
    opacity: 0.4;
    pointer-events: none;
}

.card-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 4s ease-in-out infinite;
    pointer-events: none;
}

@keyframes shine {
    0%, 100% { left: -100%; }
    50% { left: 150%; }
}

.card-logo {
    position: absolute;
    top: 25px;
    left: 30px;
    width: 65px;
    height: 65px;
    background: rgba(255,255,255,0.25);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(12px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-size: 36px;
}

.card-level {
    position: absolute;
    top: 28px;
    right: 30px;
    background: rgba(255,255,255,0.98);
    padding: 10px 24px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 700;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.card-cashback {
    position: absolute;
    top: 68px;
    right: 30px;
    font-size: 11px;
    color: rgba(255,255,255,0.9);
    font-weight: 600;
}

.card-number {
    position: absolute;
    top: 125px;
    left: 30px;
    font-size: 24px;
    font-weight: 600;
    color: white;
    letter-spacing: 4px;
    font-family: 'Courier New', monospace;
    text-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

.card-holder {
    position: absolute;
    bottom: 60px;
    left: 30px;
}

.card-holder-label {
    font-size: 10px;
    color: rgba(255,255,255,0.8);
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}

.card-holder-name {
    font-size: 17px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

.card-balance {
    position: absolute;
    bottom: 60px;
    right: 30px;
    text-align: right;
}

.card-balance-label {
    font-size: 10px;
    color: rgba(255,255,255,0.8);
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}

.card-balance-amount {
    font-size: 30px;
    font-weight: 700;
    color: white;
    text-shadow: 0 3px 8px rgba(0,0,0,0.3);
}

.card-qr {
    position: absolute;
    bottom: 25px;
    right: 30px;
    width: 75px;
    height: 75px;
    background: white;
    padding: 7px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #666;
}

.card-member-since {
    position: absolute;
    bottom: 30px;
    left: 30px;
    font-size: 11px;
    color: rgba(255,255,255,0.85);
    font-weight: 500;
}

/* Прогресс */
.level-progress {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 30px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.current-level {
    font-size: 20px;
    font-weight: 700;
    color: var(--dark-text);
}

.next-level {
    font-size: 14px;
    color: var(--gray-text);
}

.progress-bar {
    height: 12px;
    background: var(--light-gray);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 15px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-green), #5BAE49);
    border-radius: 6px;
    transition: width 1s ease;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: var(--gray-text);
}

/* Уровни */
.levels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.level-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 3px solid transparent;
    transition: all 0.3s;
}

.level-card.active {
    border-color: var(--primary-green);
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.25);
}

.level-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.level-range {
    font-size: 14px;
    color: var(--gray-text);
    margin-bottom: 20px;
}

.level-benefits {
    list-style: none;
    padding: 0;
}

.level-benefits li {
    padding: 8px 0;
    font-size: 14px;
    color: var(--dark-text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.level-benefits li i {
    color: var(--primary-green);
    width: 16px;
}

/* История транзакций */
.transactions-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 25px;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--light-gray);
}

.transaction-item:last-child {
    border-bottom: none;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-right: 15px;
}

.transaction-icon.earn {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.transaction-icon.spend {
    background: #FFE5E5;
    color: var(--red-discount);
}

.transaction-info {
    flex: 1;
}

.transaction-desc {
    font-size: 15px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 3px;
}

.transaction-date {
    font-size: 13px;
    color: var(--gray-text);
}

.transaction-amount {
    font-size: 18px;
    font-weight: 700;
}

.transaction-amount.positive {
    color: var(--primary-green);
}

.transaction-amount.negative {
    color: var(--red-discount);
}

@media (max-width: 768px) {
    .loyalty-card {
        height: 230px;
        padding: 24px;
    }
    
    .card-number {
        font-size: 18px;
        top: 100px;
        letter-spacing: 2px;
    }
    
    .card-holder-name {
        font-size: 14px;
    }
    
    .card-balance-amount {
        font-size: 24px;
    }
    
    .card-qr {
        width: 60px;
        height: 60px;
    }
    
    .levels-grid {
        grid-template-columns: 1fr;
    }
    
    .transactions-section {
        padding: 20px;
    }
}
</style>

<div class="loyalty-page">
    <div class="container">
        <h1 style="font-size: 32px; font-weight: 700; color: var(--dark-green); text-align: center; margin-bottom: 10px;">
            Программа лояльности
        </h1>
        <p style="text-align: center; color: var(--gray-text); margin-bottom: 30px;">
            Копите бонусы и получайте больше привилегий
        </p>
        
        <!-- Карта лояльности -->
        <div class="loyalty-card-container">
            <div class="loyalty-card <?php echo $loyalty_card['current_level']; ?>">
                <div class="card-shine"></div>
                <div class="card-logo">🍃</div>
                <div class="card-level"><?php echo $level_info['name']; ?></div>
                <div class="card-cashback">Кешбэк <?php echo $level_info['cashback_percent']; ?>%</div>
                <div class="card-number"><?php echo e($loyalty_card['card_number']); ?></div>
                <div class="card-holder">
                    <div class="card-holder-label">Владелец карты</div>
                    <div class="card-holder-name">
                        <?php echo e(mb_strtoupper($user['first_name'] . ' ' . $user['last_name'])); ?>
                    </div>
                </div>
                <div class="card-balance">
                    <div class="card-balance-label">Баланс</div>
                    <div class="card-balance-amount"><?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?></div>
                </div>
                <div class="card-qr">QR CODE</div>
                <div class="card-member-since">
                    С <?php echo formatDate($loyalty_card['created_at'], 'm.Y'); ?>
                </div>
            </div>
        </div>
        
        <!-- Прогресс -->
        <?php if ($next_level): ?>
        <div class="level-progress">
            <div class="progress-header">
                <span class="current-level">Ваш уровень: <?php echo $level_info['name']; ?></span>
                <span class="next-level">Следующий: <?php echo $next_level['name']; ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo min(100, $progress_percent); ?>%"></div>
            </div>
            <div class="progress-info">
                <span>Потрачено: <?php echo formatPrice($current_spent); ?></span>
                <span>До следующего уровня: <?php echo formatPrice($next_level['min_spent'] - $current_spent); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Все уровни -->
        <h2 class="section-title">Уровни программы лояльности</h2>
        <div class="levels-grid">
            <?php foreach (LOYALTY_LEVELS as $level => $info): ?>
            <div class="level-card <?php echo $level === $loyalty_card['current_level'] ? 'active' : ''; ?>">
                <div class="level-name" style="color: <?php echo ['bronze' => '#CD853F', 'silver' => '#C0C0C0', 'gold' => '#FFD700', 'platinum' => '#9370DB'][$level]; ?>">
                    <?php echo $info['name']; ?>
                </div>
                <div class="level-range">
                    От <?php echo formatPrice($info['min_spent']); ?>
                    <?php if ($info['max_spent'] < PHP_INT_MAX): ?>
                    до <?php echo formatPrice($info['max_spent']); ?>
                    <?php endif; ?>
                </div>
                <ul class="level-benefits">
                    <li><i class="fas fa-check"></i> Кешбэк <?php echo $info['cashback_percent']; ?>%</li>
                    <li><i class="fas fa-check"></i> <?php echo $info['daily_spins']; ?> вращений колеса в день</li>
                    <?php if ($level === 'silver'): ?>
                    <li><i class="fas fa-check"></i> Персональные скидки</li>
                    <?php elseif ($level === 'gold'): ?>
                    <li><i class="fas fa-check"></i> Функция "Любимый товар"</li>
                    <li><i class="fas fa-check"></i> Бесплатная доставка от 2000₽</li>
                    <?php elseif ($level === 'platinum'): ?>
                    <li><i class="fas fa-check"></i> Эксклюзивные товары</li>
                    <li><i class="fas fa-check"></i> Бесплатная экспресс-доставка</li>
                    <li><i class="fas fa-check"></i> Персональный менеджер</li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- История транзакций -->
        <div class="transactions-section">
            <h2 class="section-title">История бонусов</h2>
            <?php if (empty($transactions)): ?>
            <p style="text-align: center; color: var(--gray-text); padding: 40px;">
                История транзакций пуста
            </p>
            <?php else: ?>
                <?php foreach ($transactions as $trans): ?>
                <div class="transaction-item" style="display: flex; align-items: center;">
                    <div class="transaction-icon <?php echo $trans['type'] === 'spend' ? 'spend' : 'earn'; ?>">
                        <i class="fas fa-<?php echo $trans['type'] === 'spend' ? 'minus' : 'plus'; ?>"></i>
                    </div>
                    <div class="transaction-info">
                        <div class="transaction-desc"><?php echo e($trans['description']); ?></div>
                        <div class="transaction-date"><?php echo formatDateTime($trans['created_at']); ?></div>
                    </div>
                    <div class="transaction-amount <?php echo $trans['amount'] > 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $trans['amount'] > 0 ? '+' : ''; ?><?php echo number_format($trans['amount'], 0, '.', ' '); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>