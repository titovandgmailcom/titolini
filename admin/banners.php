<?php
/**
 * ═══════════════════════════════════════════════════════════
 * УПРАВЛЕНИЕ БАННЕРАМИ И АКЦИЯМИ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Управление баннерами - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('admin');
?>

<style>
.banners-page {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.placeholder {
    text-align: center;
    padding: 80px 20px;
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
</style>

<div class="banners-page">
    <div class="container">
        <div class="page-header">
            <h1>Управление баннерами</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Панель администратора</a>
                <span>/</span>
                <span>Баннеры</span>
            </div>
        </div>
        
        <div class="section">
            <div class="placeholder">
                <i class="fas fa-image"></i>
                <h3>Управление баннерами и акциями</h3>
                <p>Функционал находится в разработке</p>
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="btn-add">
                    <i class="fas fa-arrow-left"></i> Вернуться на главную
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>