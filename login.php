<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА ВХОДА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';

// Если уже авторизован - перенаправить
if (isLoggedIn()) {
    redirect(getRedirectByRole($_SESSION['user_role']));
}

$error = '';
$success_message = '';
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : null;

// Проверка Flash-сообщений из сессии
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Проверка ошибки входа через Google
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        $result = login($email, $password, $remember);
        
        if ($result['success']) {
            // Перенаправить на нужную страницу
            if ($redirect_url && strpos($redirect_url, '/') === 0) {
                redirect(SITE_URL . $redirect_url);
            } else {
                redirect($result['redirect']);
            }
        } else {
            $error = $result['error'];
        }
    }
}

// URL для Google OAuth - ВХОД
$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'state' => 'login',
    'prompt' => 'select_account'
]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Райский уголок</title>
    
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
            max-width: 450px;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: #F0F8EE;
            border: 1px solid #6BBF59;
            color: #2D5016;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
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
        
        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #6BBF59;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
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
        
        @media (max-width: 500px) {
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
            <h1>Вход в аккаунт</h1>
            <p class="auth-subtitle">Рады видеть вас снова!</p>
            
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo e($error); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo e($success_message); ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="your@email.com"
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Введите пароль"
                           required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Запомнить меня</label>
                </div>
                
                <button type="submit" class="btn-primary">
                    Войти
                </button>
                
                <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="forgot-link">
                    Забыли пароль?
                </a>
            </form>
            
            <div class="divider">
                <span>или</span>
            </div>
            
            <a href="<?php echo $google_auth_url; ?>" class="btn-google">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Войти через Google
            </a>
            
            <div class="auth-footer">
                <p>Еще нет аккаунта?</p>
                <a href="<?php echo SITE_URL; ?>/register.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>" class="btn-secondary">
                    Зарегистрироваться
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Валидация формы
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Пожалуйста, заполните все поля');
            }
        });
    </script>
</body>
</html>