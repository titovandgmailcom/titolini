<?php
$page_title = 'Котировки - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('supplier');
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

.placeholder {
    background: white;
    padding: 80px 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.placeholder i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.placeholder h3 {
    font-size: 24px;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.placeholder p {
    color: var(--gray-text);
    margin-bottom: 30px;
}

.btn-back {
    display: inline-block;
    padding: 12px 24px;
    background: #FF6B35;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.btn-back:hover {
    background: #E55A2B;
}
</style>

<div class="supplier-page">
    <div class="container">
        <div class="page-header">
            <h1>Котировки</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/supplier/dashboard.php">Кабинет поставщика</a>
                <span>/</span>
                <span>Котировки</span>
            </div>
        </div>
        
        <div class="placeholder">
            <i class="fas fa-file-invoice"></i>
            <h3>Котировки и запросы на поставку</h3>
            <p>Функционал находится в разработке</p>
            <a href="<?php echo SITE_URL; ?>/supplier/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Вернуться назад
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>