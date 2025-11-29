<?php
$page_title = 'Чаты - Райский уголок';
require_once __DIR__ . '/../includes/header.php';
requireRole('manager');
?>

<div style="padding: 80px 20px; text-align: center; background: #f5f5f5; min-height: 60vh;">
    <i class="fas fa-comments" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
    <h2>Чаты с клиентами</h2>
    <p style="color: #999;">Функционал в разработке</p>
    <a href="<?php echo SITE_URL; ?>/manager/dashboard.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #9C27B0; color: white; border-radius: 8px; text-decoration: none;">Назад</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>