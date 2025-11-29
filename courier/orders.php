<?php
$page_title = 'Мои заказы - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('courier');
?>

<div style="padding: 80px 20px; text-align: center; background: #f5f5f5; min-height: 60vh;">
    <i class="fas fa-box" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
    <h2>Список заказов</h2>
    <p style="color: #999;">Функционал в разработке</p>
    <a href="<?php echo SITE_URL; ?>/courier/dashboard.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: var(--primary-green); color: white; border-radius: 8px; text-decoration: none;">Назад</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>