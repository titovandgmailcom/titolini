<?php
$page_title = 'Профиль поставщика - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('supplier');

$user = getCurrentUser();
?>

<style>
.supplier-page {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.page-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.breadcrumb {
    display: flex;
    gap: 10px;
    font-size: 14px;
    color: var(--gray-text);
    margin-top: 10px;
}

.breadcrumb a {
    color: #FF6B35;
    text-decoration: none;
}

.profile-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #FF6B35;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: 700;
}

.profile-info h2 {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 5px;
}

.profile-info p {
    color: var(--gray-text);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-item {
    padding: 20px;
    background: #F9F9F9;
    border-radius: 8px;
}

.info-label {
    font-size: 13px;
    color: var(--gray-text);
    margin-bottom: 8px;
    font-weight: 600;
}

.info-value {
    font-size: 16px;
    color: var(--dark-text);
    font-weight: 600;
}

.btn-edit {
    padding: 12px 24px;
    background: #FF6B35;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-edit:hover {
    background: #E55A2B;
}
</style>

<div class="supplier-page">
    <div class="container">
        <div class="page-header">
            <h1>Профиль поставщика</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/supplier/dashboard.php">Кабинет поставщика</a>
                <span>/</span>
                <span>Профиль</span>
            </div>
        </div>
        
        <div class="profile-section">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p>Поставщик • ID: <?php echo $user['id']; ?></p>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo e($user['email']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Телефон</div>
                    <div class="info-value"><?php echo e($user['phone'] ?? 'Не указан'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Дата регистрации</div>
                    <div class="info-value"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Статус</div>
                    <div class="info-value">
                        <span style="color: #4CAF50;">✓ Активный</span>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="#" class="btn-edit" onclick="alert('Редактирование профиля - в разработке'); return false;">
                    <i class="fas fa-edit"></i> Редактировать профиль
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>