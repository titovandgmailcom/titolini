<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СБРОС ПАРОЛЯ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';

// Если уже авторизован - редирект
if (isLoggedIn()) {
    redirect('/index.php');
}

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;
$valid_token = false;
$user_id = null;

// Проверить токен
if (!empty($token)) {
    $stmt = $pdo->prepare("
        SELECT pr.*, u.email, u.first_name
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        $valid_token = true;
        $user_id = $reset['user_id'];
    }
}

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password)) {
        $errors[] = 'Введите новый пароль';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов';
    } elseif ($password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Обновить пароль
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);
            
            // Пометить токен как использованный
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $pdo->commit();
            $success = true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Ошибка при сбросе пароля. Попробуйте позже.';
            error_log("Password Reset Error: " . $e->getMessage());
        }
    }
}

$page_title = 'Новый пароль - Райский уголок';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.reset-password-page {
    min-height: calc(100vh - 400px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.reset-password-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    padding: 40px;
    max-width: 480px;
    width: 100%;
}

.reset-password-icon {
    width: 80px;
    height: 80px;
    background: var(--light-green-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 36px;
}

.reset-password-icon.error {
    background: #FFEBEE;
    color: var(--red-discount);
}

.reset-password-icon.success {
    background: #E8F5E9;
    color: var(--primary-green);
}

.reset-password-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    text-align: center;
    margin-bottom: 12px;
}

.reset-password-subtitle {
    font-size: 15px;
    color: var(--gray-text);
    text-align: center;
    margin-bottom: 32px;
    line-height: 1.6;
}

.success-message {
    background: #E8F5E9;
    border: 2px solid var(--primary-green);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    text-align: center;
}

.success-message h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 8px;
}

.success-message p {
    color: var(--dark-text);
    font-size: 15px;
    margin-bottom: 20px;
}

.error-box {
    background: #FFEBEE;
    border: 2px solid var(--red-discount);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    text-align: center;
}

.error-box h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--red-discount);
    margin-bottom: 8px;
}

.error-box p {
    color: var(--dark-text);
    font-size: 15px;
}

.error-message {
    background: #FFEBEE;
    border: 2px solid var(--red-discount);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
}

.error-message ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-message li {
    color: var(--red-discount);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
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

.password-strength {
    margin-top: 8px;
    font-size: 13px;
}

.password-hint {
    color: var(--gray-text);
    font-size: 13px;
    margin-top: 4px;
}

.btn-submit {
    width: 100%;
    padding: 16px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 8px;
}

.btn-submit:hover {
    background: #5BAE49;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 191, 89, 0.3);
}

.btn-login {
    width: 100%;
    padding: 16px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s;
}

.btn-login:hover {
    background: #5BAE49;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 191, 89, 0.3);
}

.back-to-login {
    text-align: center;
    margin-top: 24px;
    font-size: 14px;
    color: var(--gray-text);
}

.back-to-login a {
    color: var(--primary-green);
    font-weight: 600;
    text-decoration: none;
}

@media (max-width: 768px) {
    .reset-password-container {
        padding: 32px 24px;
    }
}
</style>

<div class="reset-password-page">
    <div class="reset-password-container">
        <?php if (!$valid_token): ?>
            <div class="reset-password-icon error">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1 class="reset-password-title">Ссылка недействительна</h1>
            <div class="error-box">
                <h3>Ошибка</h3>
                <p>Ссылка для сброса пароля недействительна или истекла. Пожалуйста, запросите новую ссылку.</p>
            </div>
            <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="btn-login">
                Запросить новую ссылку
            </a>
        <?php elseif ($success): ?>
            <div class="reset-password-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="reset-password-title">Пароль изменён!</h1>
            <div class="success-message">
                <h3>Успешно</h3>
                <p>Ваш пароль успешно изменён. Теперь вы можете войти в систему с новым паролем.</p>
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn-login">
                    Войти в систему
                </a>
            </div>
        <?php else: ?>
            <div class="reset-password-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="reset-password-title">Новый пароль</h1>
            <p class="reset-password-subtitle">
                Придумайте надёжный пароль для вашего аккаунта
            </p>
            
            <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li>
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo e($error); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Новый пароль</label>
                    <input type="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Введите новый пароль"
                           required
                           autofocus>
                    <div class="password-hint">
                        Минимум <?php echo PASSWORD_MIN_LENGTH; ?> символов
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Подтвердите пароль</label>
                    <input type="password" 
                           name="password_confirm" 
                           class="form-input" 
                           placeholder="Повторите пароль"
                           required>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Сбросить пароль
                </button>
            </form>
        <?php endif; ?>
        
        <div class="back-to-login">
            <i class="fas fa-arrow-left"></i>
            <a href="<?php echo SITE_URL; ?>/login.php">Вернуться ко входу</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>