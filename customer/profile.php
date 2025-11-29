<?php
/**
 * ═══════════════════════════════════════════════════════════
 * НАСТРОЙКИ ПРОФИЛЯ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Настройки профиля - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

requireRole('customer');

$user = getCurrentUser();
$errors = [];

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    
    // ВАЖНО: Email и телефон НЕ обрабатываем - они защищены
    
    if (empty($first_name)) $errors[] = 'Введите имя';
    if (empty($last_name)) $errors[] = 'Введите фамилию';
    
    if (empty($errors)) {
        // Обновляем ТОЛЬКО имя, фамилию и дату рождения
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, birth_date = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$first_name, $last_name, $birth_date, $_SESSION['user_id']])) {
            setFlash('success', 'Профиль успешно обновлен');
            redirect('/customer/profile.php');
        } else {
            $errors[] = 'Ошибка при обновлении профиля';
        }
    }
}

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($old_password)) $errors[] = 'Введите текущий пароль';
    if (empty($new_password)) $errors[] = 'Введите новый пароль';
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов';
    }
    if ($new_password !== $confirm_password) $errors[] = 'Пароли не совпадают';
    
    if (empty($errors)) {
        $result = changePassword($_SESSION['user_id'], $old_password, $new_password);
        if ($result['success']) {
            setFlash('success', 'Пароль успешно изменен');
            redirect('/customer/profile.php');
        } else {
            $errors[] = $result['error'];
        }
    }
}

$user = getCurrentUser();
?>

<style>
.profile-page {
    padding: 30px 0 60px;
}

.profile-container {
    max-width: 800px;
    margin: 0 auto;
}

.profile-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 30px;
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary-green);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s;
}

.form-input:focus {
    border-color: var(--primary-green);
    outline: none;
    box-shadow: 0 0 0 3px rgba(107, 191, 89, 0.1);
}

.form-input:disabled,
.form-input:read-only {
    background: #F5F5F5;
    color: #999;
    cursor: not-allowed;
    border-color: #E5E5E5;
}

.security-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
    padding: 8px 12px;
    background: #FFF9E6;
    border-left: 3px solid #FFB800;
    border-radius: 4px;
    font-size: 13px;
    color: #CC8800;
}

.security-notice i {
    font-size: 14px;
}

.btn-primary {
    padding: 14px 32px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

.error-box {
    background: #FFF3F3;
    border: 1px solid #FFD6D6;
    color: var(--red-discount);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .profile-section {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-page">
    <div class="container">
        <div class="profile-container">
            <h1 style="font-size: 32px; font-weight: 700; color: var(--dark-green); margin-bottom: 30px;">
                Настройки профиля
            </h1>
            
            <?php if (!empty($errors)): ?>
            <div class="error-box">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Личные данные -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Личные данные
                </h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Имя</label>
                            <input type="text" 
                                   name="first_name" 
                                   class="form-input" 
                                   value="<?php echo e($user['first_name']); ?>" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Фамилия</label>
                            <input type="text" 
                                   name="last_name" 
                                   class="form-input" 
                                   value="<?php echo e($user['last_name']); ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" 
                               class="form-input" 
                               value="<?php echo e($user['email']); ?>" 
                               readonly>
                        <div class="security-notice">
                            <i class="fas fa-shield-alt"></i>
                            <span>Email нельзя изменить по соображениям безопасности</span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <input type="tel" 
                                   class="form-input" 
                                   value="<?php echo e($user['phone'] ?? 'Не указан'); ?>" 
                                   readonly>
                            <div class="security-notice">
                                <i class="fas fa-shield-alt"></i>
                                <span>Телефон нельзя изменить для защиты от мошенничества</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Дата рождения</label>
                            <input type="date" 
                                   name="birth_date" 
                                   class="form-input" 
                                   value="<?php echo e($user['birth_date'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                </form>
            </div>
            
            <!-- Смена пароля -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Изменить пароль
                </h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Текущий пароль</label>
                        <input type="password" 
                               name="old_password" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" 
                                   name="new_password" 
                                   class="form-input" 
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                                   placeholder="Минимум <?php echo PASSWORD_MIN_LENGTH; ?> символов"
                                   required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Повторите пароль</label>
                            <input type="password" 
                                   name="confirm_password" 
                                   class="form-input" 
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-primary">
                        <i class="fas fa-key"></i> Изменить пароль
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>