<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА РЕГИСТРАЦИИ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/email.php';

// Если уже авторизован - перенаправить
if (isLoggedIn()) {
    redirect(getRedirectByRole($_SESSION['user_role']));
}

$errors = [];
$success = false;
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : null;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Валидация
    if (empty($first_name)) $errors[] = 'Введите имя';
    if (empty($last_name)) $errors[] = 'Введите фамилию';
    if (empty($email)) $errors[] = 'Введите email';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (empty($password)) $errors[] = 'Введите пароль';
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов';
    }
    if ($password !== $password_confirm) $errors[] = 'Пароли не совпадают';
    if (!$agree_terms) $errors[] = 'Необходимо принять условия пользовательского соглашения';
    
    // Если нет ошибок - регистрировать
    if (empty($errors)) {
        $result = registerUser($email, $password, $first_name, $last_name, $phone);
        
        if ($result['success']) {
            $success = true;
        } else {
            $errors[] = $result['error'];
        }
    }
}

// URL для Google OAuth - РЕГИСТРАЦИЯ
$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'state' => 'register',
    'prompt' => 'select_account'
]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Райский уголок</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', 'Arial', sans-serif;
            background: linear-gradient(135deg, #F0F8EE 0%, #E8F5E9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 550px;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-logo-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        
        .auth-logo-text {
            font-size: 28px;
            font-weight: 700;
            color: #6BBF59;
            letter-spacing: -0.5px;
        }
        
        .auth-box {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .auth-box h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2D5016;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .auth-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 15px;
        }
        
        .error-message {
            background: #FFF3F3;
            border: 1px solid #FFD6D6;
            color: #E31E24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .error-message li {
            margin: 5px 0;
        }
        
        .success-message {
            background: #F0F8EE;
            border: 1px solid #6BBF59;
            color: #2D5016;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 48px;
            color: #6BBF59;
            margin-bottom: 15px;
        }
        
        .success-message h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2D5016;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E5E5E5;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            border-color: #6BBF59;
            outline: none;
            box-shadow: 0 0 0 3px rgba(107, 191, 89, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .checkbox-group label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            line-height: 1.5;
        }
        
        .checkbox-group label a {
            color: #6BBF59;
            text-decoration: none;
            font-weight: 600;
        }
        
        .checkbox-group label a:hover {
            text-decoration: underline;
        }
        
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: #6BBF59;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .btn-primary:hover {
            background: #5BAE49;
            transform: scale(1.02);
        }
        
        .divider {
            margin: 24px 0;
            text-align: center;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #E5E5E5;
        }
        
        .divider span {
            position: relative;
            background: white;
            padding: 0 16px;
            color: #666;
            font-size: 14px;
        }
        
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            color: #333;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-family: inherit;
            font-size: 15px;
        }
        
        .btn-google:hover {
            background: #f8f9fa;
            border-color: #6BBF59;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .auth-footer {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #E5E5E5;
            text-align: center;
        }
        
        .auth-footer p {
            color: #666;
            margin-bottom: 16px;
        }
        
        .btn-secondary {
            width: 100%;
            padding: 16px;
            background: white;
            color: #6BBF59;
            border: 2px solid #6BBF59;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-secondary:hover {
            background: #F0F8EE;
        }
        
        .benefits-list {
            background: #F0F8EE;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .benefits-list h3 {
            font-size: 16px;
            font-weight: 700;
            color: #2D5016;
            margin-bottom: 12px;
        }
        
        .benefits-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .benefits-list li {
            color: #666;
            margin: 8px 0;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .auth-box {
                padding: 30px 20px;
            }
            
            .auth-box h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">
            <div class="auth-logo-icon">🍃</div>
            <div class="auth-logo-text">Райский уголок</div>
        </div>
        
        <div class="auth-box">
            <h1>Регистрация</h1>
            <p class="auth-subtitle">Создайте аккаунт и получите 100 бонусов!</p>
            
            <?php if ($success): ?>
            <!-- УСПЕШНАЯ ОБЫЧНАЯ РЕГИСТРАЦИЯ -->
            <div class="success-message">
                <div class="success-icon">✉️</div>
                <h3>Регистрация успешна!</h3>
                <p>На ваш email отправлено письмо с подтверждением. Пожалуйста, перейдите по ссылке в письме для активации аккаунта.</p>
                <p style="margin-top: 20px;">
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn-secondary">
                        Перейти ко входу
                    </a>
                </p>
            </div>
            
            <?php elseif (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
            <!-- УСПЕШНАЯ РЕГИСТРАЦИЯ ЧЕРЕЗ GOOGLE -->
            <?php 
            $reg_email = $_SESSION['registration_email'] ?? '';
            unset($_SESSION['registration_success']);
            unset($_SESSION['registration_email']);
            ?>
            <div class="success-message">
                <div class="success-icon">✉️</div>
                <h3>Регистрация почти завершена!</h3>
                <p>На ваш email <strong><?php echo e($reg_email); ?></strong> отправлено письмо с подтверждением.</p>
                <p>Пожалуйста, перейдите по ссылке в письме для активации аккаунта.</p>
                <p style="margin-top: 20px;">
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn-secondary">
                        Перейти ко входу
                    </a>
                </p>
            </div>
            
            <?php elseif (isset($_SESSION['google_error'])): ?>
            <!-- ОШИБКА ОТ GOOGLE OAUTH -->
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                echo $_SESSION['google_error']; 
                unset($_SESSION['google_error']);
                ?>
            </div>
            <p style="text-align: center; margin: 20px 0;">
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn-secondary">
                    Перейти на страницу входа
                </a>
            </p>

            <?php else: ?>
            <!-- ФОРМА РЕГИСТРАЦИИ -->
            
            <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Ошибки при регистрации:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="benefits-list">
                <h3>Преимущества регистрации:</h3>
                <ul>
                    <li>🎁 100 бонусов на первый заказ</li>
                    <li>💳 Кешбэк до 7% с каждой покупки</li>
                    <li>🎰 Колесо фортуны каждый день</li>
                    <li>♻️ Участие в эко-программе</li>
                    <li>🚚 Быстрая доставка</li>
                </ul>
            </div>
            
            <a href="<?php echo $google_auth_url; ?>" class="btn-google">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Регистрация через Google
            </a>
            
            <div class="divider">
                <span>или</span>
            </div>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Имя *</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-input" 
                               placeholder="Иван"
                               value="<?php echo isset($_POST['first_name']) ? e($_POST['first_name']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Фамилия *</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-input" 
                               placeholder="Петров"
                               value="<?php echo isset($_POST['last_name']) ? e($_POST['last_name']) : ''; ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="your@email.com"
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-input" 
                           placeholder="+7 (999) 123-45-67"
                           value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Пароль *</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Минимум <?php echo PASSWORD_MIN_LENGTH; ?> символов"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Повторите пароль *</label>
                        <input type="password" 
                               id="password_confirm" 
                               name="password_confirm" 
                               class="form-input" 
                               placeholder="Повторите пароль"
                               required>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" 
                           id="agree_terms" 
                           name="agree_terms" 
                           required>
                    <label for="agree_terms">
                        Я согласен с 
                        <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank">условиями использования</a> 
                        и 
                        <a href="<?php echo SITE_URL; ?>/privacy.php" target="_blank">политикой конфиденциальности</a>
                    </label>
                </div>
                
                <button type="submit" class="btn-primary">
                    Зарегистрироваться
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Уже есть аккаунт?</p>
                <a href="<?php echo SITE_URL; ?>/login.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>" class="btn-secondary">
                    Войти
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Валидация формы
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            const agreeTerms = document.getElementById('agree_terms').checked;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                return;
            }
            
            if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
                e.preventDefault();
                alert('Пароль должен содержать минимум <?php echo PASSWORD_MIN_LENGTH; ?> символов');
                return;
            }
            
            if (!agreeTerms) {
                e.preventDefault();
                alert('Необходимо принять условия использования');
                return;
            }
        });
        
        // Маска для телефона
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value[0] === '7' || value[0] === '8') {
                        value = '7' + value.slice(1);
                    }
                    let formatted = '+7';
                    if (value.length > 1) {
                        formatted += ' (' + value.slice(1, 4);
                    }
                    if (value.length >= 5) {
                        formatted += ') ' + value.slice(4, 7);
                    }
                    if (value.length >= 8) {
                        formatted += '-' + value.slice(7, 9);
                    }
                    if (value.length >= 10) {
                        formatted += '-' + value.slice(9, 11);
                    }
                    e.target.value = formatted;
                }
            });
        }
    </script>
</body>
</html>