<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ЗАБЫЛИ ПАРОЛЬ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Если уже авторизован - редирект
if (isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    } else {
        // Проверить существование пользователя
        $stmt = $pdo->prepare("SELECT id, email, first_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Генерация токена сброса
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Удалить старые токены
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            // Создать новый токен
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['id'], $token, $expires]);
            
            // Отправить email
            $reset_link = SITE_URL . "/reset-password.php?token=$token";
            
            $subject = "Сброс пароля - Райский уголок";
            $message = "
                <h2>Здравствуйте, {$user['first_name']}!</h2>
                <p>Вы запросили сброс пароля на сайте Райский уголок.</p>
                <p>Для сброса пароля перейдите по ссылке:</p>
                <p><a href='$reset_link' style='background: #6BBF59; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Сбросить пароль</a></p>
                <p>Или скопируйте ссылку в браузер:</p>
                <p>$reset_link</p>
                <p><strong>Ссылка действительна 1 час.</strong></p>
                <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
                <hr>
                <p style='color: #999; font-size: 12px;'>С уважением,<br>Команда Райский уголок</p>
            ";
            
            if (sendEmail($email, $subject, $message)) {
                $success = true;
            } else {
                $errors[] = 'Ошибка отправки email. Попробуйте позже.';
            }
        } else {
            // Не говорим что email не найден (защита от перебора)
            $success = true;
        }
    }
}

$page_title = 'Восстановление пароля - Райский уголок';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.forgot-password-page {
    min-height: calc(100vh - 400px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.forgot-password-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    padding: 40px;
    max-width: 480px;
    width: 100%;
}

.forgot-password-icon {
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

.forgot-password-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    text-align: center;
    margin-bottom: 12px;
}

.forgot-password-subtitle {
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

.success-message i {
    font-size: 48px;
    color: var(--primary-green);
    margin-bottom: 12px;
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
    line-height: 1.6;
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

.error-message li:last-child {
    margin-bottom: 0;
}

.form-group {
    margin-bottom: 24px;
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
}

.btn-submit:hover {
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

.back-to-login a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .forgot-password-container {
        padding: 32px 24px;
    }
}
</style>

<div class="forgot-password-page">
    <div class="forgot-password-container">
        <div class="forgot-password-icon">
            <i class="fas fa-key"></i>
        </div>
        
        <h1 class="forgot-password-title">Забыли пароль?</h1>
        <p class="forgot-password-subtitle">
            Укажите email, который вы использовали при регистрации. Мы отправим вам ссылку для сброса пароля.
        </p>
        
        <?php if ($success): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h3>Письмо отправлено!</h3>
            <p>
                Проверьте свою почту. Если аккаунт с указанным email существует, 
                мы отправили инструкции по восстановлению пароля.
            </p>
        </div>
        <?php else: ?>
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
                    <label class="form-label">Email</label>
                    <input type="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="your@email.com"
                           value="<?php echo e($_POST['email'] ?? ''); ?>"
                           required
                           autofocus>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    Отправить ссылку для сброса
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