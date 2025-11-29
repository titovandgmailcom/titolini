<?php
/**
 * ═══════════════════════════════════════════════════════════
 * УПРАВЛЕНИЕ ПЕРСОНАЛОМ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Управление персоналом - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('director');

// Получить всех сотрудников
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE role != 'customer' 
    ORDER BY role, created_at DESC
");
$staff = $stmt->fetchAll();

// Группировка по ролям
$staff_by_role = [];
foreach ($staff as $member) {
    $staff_by_role[$member['role']][] = $member;
}
?>

<style>
.staff-page {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.role-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.role-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.role-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.role-icon.admin { background: #E3F2FD; color: #2196F3; }
.role-icon.manager { background: #F3E5F5; color: #9C27B0; }
.role-icon.supplier { background: #FFF3E6; color: var(--accent-orange); }
.role-icon.courier { background: var(--light-green-bg); color: var(--primary-green); }

.staff-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.staff-card {
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s;
}

.staff-card:hover {
    border-color: #2196F3;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
}

.staff-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.staff-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #2196F3;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
}

.staff-info h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 4px;
}

.staff-info p {
    font-size: 13px;
    color: var(--gray-text);
}

.staff-details {
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.detail-label {
    color: var(--gray-text);
}

.detail-value {
    font-weight: 600;
    color: var(--dark-text);
}
</style>

<div class="staff-page">
    <div class="container">
        <div class="page-header">
            <h1>Управление персоналом</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/director/dashboard.php">Панель директора</a>
                <span>/</span>
                <span>Сотрудники</span>
            </div>
        </div>
        
        <?php if (isset($staff_by_role['admin'])): ?>
        <div class="role-section">
            <div class="role-header">
                <div class="role-icon admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Администраторы (<?php echo count($staff_by_role['admin']); ?>)</h2>
            </div>
            
            <div class="staff-grid">
                <?php foreach ($staff_by_role['admin'] as $member): ?>
                <div class="staff-card">
                    <div class="staff-header">
                        <div class="staff-avatar">
                            <?php echo strtoupper(substr($member['first_name'], 0, 1)); ?>
                        </div>
                        <div class="staff-info">
                            <h3><?php echo e($member['first_name'] . ' ' . $member['last_name']); ?></h3>
                            <p><?php echo e($member['email']); ?></p>
                        </div>
                    </div>
                    <div class="staff-details">
                        <div class="detail-row">
                            <span class="detail-label">Телефон:</span>
                            <span class="detail-value"><?php echo e($member['phone'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Дата найма:</span>
                            <span class="detail-value"><?php echo date('d.m.Y', strtotime($member['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($staff_by_role['manager'])): ?>
        <div class="role-section">
            <div class="role-header">
                <div class="role-icon manager">
                    <i class="fas fa-headset"></i>
                </div>
                <h2>Менеджеры (<?php echo count($staff_by_role['manager']); ?>)</h2>
            </div>
            
            <div class="staff-grid">
                <?php foreach ($staff_by_role['manager'] as $member): ?>
                <div class="staff-card">
                    <div class="staff-header">
                        <div class="staff-avatar" style="background: #9C27B0;">
                            <?php echo strtoupper(substr($member['first_name'], 0, 1)); ?>
                        </div>
                        <div class="staff-info">
                            <h3><?php echo e($member['first_name'] . ' ' . $member['last_name']); ?></h3>
                            <p><?php echo e($member['email']); ?></p>
                        </div>
                    </div>
                    <div class="staff-details">
                        <div class="detail-row">
                            <span class="detail-label">Телефон:</span>
                            <span class="detail-value"><?php echo e($member['phone'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Дата найма:</span>
                            <span class="detail-value"><?php echo date('d.m.Y', strtotime($member['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($staff_by_role['courier'])): ?>
        <div class="role-section">
            <div class="role-header">
                <div class="role-icon courier">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h2>Курьеры (<?php echo count($staff_by_role['courier']); ?>)</h2>
            </div>
            
            <div class="staff-grid">
                <?php foreach ($staff_by_role['courier'] as $member): ?>
                <div class="staff-card">
                    <div class="staff-header">
                        <div class="staff-avatar" style="background: var(--primary-green);">
                            <?php echo strtoupper(substr($member['first_name'], 0, 1)); ?>
                        </div>
                        <div class="staff-info">
                            <h3><?php echo e($member['first_name'] . ' ' . $member['last_name']); ?></h3>
                            <p><?php echo e($member['email']); ?></p>
                        </div>
                    </div>
                    <div class="staff-details">
                        <div class="detail-row">
                            <span class="detail-label">Телефон:</span>
                            <span class="detail-value"><?php echo e($member['phone'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Дата найма:</span>
                            <span class="detail-value"><?php echo date('d.m.Y', strtotime($member['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>