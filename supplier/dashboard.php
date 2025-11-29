<?php
$page_title = 'Кабинет поставщика - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('supplier');

$user = getCurrentUser();
?>

<style>
.supplier-dashboard {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.supplier-header {
    background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
}

.supplier-header h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
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
    border-color: #FF6B35;
    transform: translateY(-2px);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: #FFF3E6;
    color: var(--accent-orange);
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

<div class="supplier-dashboard">
    <div class="container">
        <div class="supplier-header">
            <h1>📦 Кабинет поставщика</h1>
            <p>Добро пожаловать, <?php echo e($user['first_name']); ?>!</p>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/supplier/profile.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="action-text">
                    <h3>Профиль</h3>
                    <p>Информация о компании</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/supplier/quotes.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="action-text">
                    <h3>Котировки</h3>
                    <p>Запросы на поставку</p>
                </div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/supplier/communications.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="action-text">
                    <h3>Сообщения</h3>
                    <p>Связь с менеджерами</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>