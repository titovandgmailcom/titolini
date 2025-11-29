<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ЭКО-ПРОГРАММА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Эко-программа - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('customer');

$user = getCurrentUser();

// Получить статистику
$eco_stats = getEcoStats($_SESSION['user_id']);

// Обработка сканирования QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_qr'])) {
    $qr_code = trim($_POST['qr_code']);
    
    if (!empty($qr_code)) {
        $result = scanQRCode($_SESSION['user_id'], $qr_code);
        
        if ($result['success']) {
            $message = "Отлично! Начислено {$result['bonus']} бонусов!";
            
            if ($result['achievement']) {
                $badge = $result['achievement']['badge'];
                $message .= " Поздравляем! Получено достижение: {$badge['name']}!";
            }
            
            setFlash('success', $message);
            redirect('/customer/eco.php');
        } else {
            setFlash('error', $result['error']);
        }
    }
}
?>

<style>
.eco-page {
    padding: 30px 0 60px;
}

.eco-header {
    text-align: center;
    margin-bottom: 40px;
}

.eco-title {
    font-size: 36px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 10px;
}

.eco-subtitle {
    font-size: 18px;
    color: var(--gray-text);
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
    text-align: center;
}

.stat-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-text);
}

.scanner-section {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 40px;
    text-align: center;
}

.scanner-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

.scanner-form {
    max-width: 500px;
    margin: 0 auto;
}

.qr-input {
    width: 100%;
    padding: 16px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    font-size: 16px;
    margin-bottom: 15px;
}

.btn-scan {
    width: 100%;
    padding: 16px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.btn-scan:hover {
    background: #5BAE49;
}

.badges-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 25px;
    text-align: center;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.badge-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    text-align: center;
    position: relative;
    border: 3px solid transparent;
    transition: all 0.3s;
}

.badge-card.earned {
    border-color: var(--primary-green);
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.25);
}

.badge-card.locked {
    opacity: 0.6;
}

.badge-icon {
    font-size: 64px;
    margin-bottom: 15px;
}

.badge-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.badge-requirement {
    font-size: 14px;
    color: var(--gray-text);
    margin-bottom: 15px;
}

.badge-progress {
    background: var(--light-gray);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.badge-progress-fill {
    height: 100%;
    background: var(--primary-green);
    transition: width 0.5s;
}

.badge-bonus {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-green);
}

.earned-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    background: var(--primary-green);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}
</style>

<div class="eco-page">
    <div class="container">
        <div class="eco-header">
            <h1 class="eco-title">Эко-программа</h1>
            <p class="eco-subtitle">Сдавай упаковку — получай бонусы и помогай планете</p>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?php echo $eco_stats['qr_scanned']; ?></div>
                <div class="stat-label">Сдано упаковок</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value"><?php echo $eco_stats['badges_earned']; ?></div>
                <div class="stat-label">Получено достижений</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value"><?php echo $eco_stats['qr_scanned'] * ECO_POINTS_PER_QR; ?></div>
                <div class="stat-label">Заработано бонусов</div>
            </div>
        </div>
        
        <!-- Сканер QR -->
        <div class="scanner-section">
            <div class="scanner-icon">📷</div>
            <h2>Отсканируйте QR-код</h2>
            <p style="color: var(--gray-text); margin-bottom: 30px;">
                Введите код с упаковки и получите <?php echo ECO_POINTS_PER_QR; ?> бонусов
            </p>
            
            <form method="POST" class="scanner-form">
                <input type="text" 
                       name="qr_code" 
                       class="qr-input" 
                       placeholder="Введите код с упаковки"
                       required>
                <button type="submit" name="scan_qr" class="btn-scan">
                    Отсканировать
                </button>
            </form>
        </div>
        
        <!-- Достижения -->
        <div class="badges-section">
            <h2 class="section-title">Эко-достижения</h2>
            
            <div class="badges-grid">
                <?php foreach (ECO_BADGES as $badge_type => $badge): ?>
                <?php 
                $is_earned = false;
                foreach ($eco_stats['badges'] as $earned) {
                    if ($earned['badge_type'] === $badge_type) {
                        $is_earned = true;
                        break;
                    }
                }
                $progress_percent = min(100, ($eco_stats['qr_scanned'] / $badge['qr_count']) * 100);
                ?>
                
                <div class="badge-card <?php echo $is_earned ? 'earned' : 'locked'; ?>">
                    <?php if ($is_earned): ?>
                    <div class="earned-badge">✓</div>
                    <?php endif; ?>
                    
                    <div class="badge-icon"><?php echo $badge['icon']; ?></div>
                    <div class="badge-name" style="color: <?php echo $badge['color']; ?>">
                        <?php echo $badge['name']; ?>
                    </div>
                    <div class="badge-requirement">
                        <?php echo $badge['qr_count']; ?> упаковок
                    </div>
                    
                    <?php if (!$is_earned): ?>
                    <div class="badge-progress">
                        <div class="badge-progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                    </div>
                    <div style="font-size: 13px; color: var(--gray-text); margin-bottom: 15px;">
                        <?php echo $eco_stats['qr_scanned']; ?> / <?php echo $badge['qr_count']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="badge-bonus">+<?php echo $badge['bonus']; ?> бонусов</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>