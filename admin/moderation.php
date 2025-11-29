<?php
/**
 * ═══════════════════════════════════════════════════════════
 * МОДЕРАЦИЯ КОНТЕНТА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Модерация - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('admin');

// Получить пользователей на модерации
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE status = 'pending' 
    ORDER BY created_at DESC
");
$pending_users = $stmt->fetchAll();

// Получить заблокированных пользователей
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE status = 'blocked' 
    ORDER BY created_at DESC 
    LIMIT 20
");
$blocked_users = $stmt->fetchAll();
?>

<style>
.moderation-page {
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

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 20px;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: #F9F9F9;
    padding: 15px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: var(--dark-text);
}

.users-table td {
    padding: 18px 15px;
    border-bottom: 1px solid #F0F0F0;
}

.user-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.user-badge.pending {
    background: #FFF3E6;
    color: var(--accent-orange);
}

.user-badge.blocked {
    background: #FFE5E5;
    color: #E31E24;
}

.btn-approve, .btn-block, .btn-unblock {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    margin-right: 8px;
}

.btn-approve {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.btn-block {
    background: #FFE5E5;
    color: #E31E24;
}

.btn-unblock {
    background: var(--light-green-bg);
    color: var(--primary-green);
}
</style>

<div class="moderation-page">
    <div class="container">
        <div class="page-header">
            <h1>Модерация контента</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Панель администратора</a>
                <span>/</span>
                <span>Модерация</span>
            </div>
        </div>
        
        <!-- Пользователи на модерации -->
        <div class="section">
            <h2 class="section-title">Пользователи на модерации (<?php echo count($pending_users); ?>)</h2>
            
            <?php if (empty($pending_users)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">Нет пользователей на модерации</p>
            <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Дата регистрации</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_users as $user): ?>
                    <tr>
                        <td><strong><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e($user['phone'] ?? '—'); ?></td>
                        <td><?php echo formatDateTime($user['created_at']); ?></td>
                        <td><span class="user-badge pending">Ожидает подтверждения</span></td>
                        <td>
                            <button class="btn-approve" onclick="alert('Функция в разработке')">
                                <i class="fas fa-check"></i> Одобрить
                            </button>
                            <button class="btn-block" onclick="alert('Функция в разработке')">
                                <i class="fas fa-ban"></i> Заблокировать
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <!-- Заблокированные пользователи -->
        <div class="section">
            <h2 class="section-title">Заблокированные пользователи (<?php echo count($blocked_users); ?>)</h2>
            
            <?php if (empty($blocked_users)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">Нет заблокированных пользователей</p>
            <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Дата блокировки</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blocked_users as $user): ?>
                    <tr>
                        <td><strong><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo formatDateTime($user['updated_at']); ?></td>
                        <td>
                            <button class="btn-unblock" onclick="alert('Функция в разработке')">
                                <i class="fas fa-unlock"></i> Разблокировать
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>