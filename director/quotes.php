<?php
/**
 * КОТИРОВКИ ПОСТАВЩИКОВ
 */
$page_title = 'Котировки - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('director');
?>

<div style="padding: 80px 20px; text-align: center; background: #f5f5f5; min-height: 60vh;">
    <i class="fas fa-file-invoice" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
    <h2>Котировки поставщиков</h2>
    <p style="color: #999;">Функционал в разработке</p>
    <a href="<?php echo SITE_URL; ?>/director/dashboard.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #2196F3; color: white; border-radius: 8px; text-decoration: none;">Назад</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>