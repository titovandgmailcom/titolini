<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА ПОДТВЕРЖДЕНИЯ EMAIL
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/email.php';

$token = $_GET['token'] ?? '';
$result = null;

if ($token) {
    $result = verifyEmail($token);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение Email - Райский уголок</title>
    
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
        
        .verify-container {
            width: 100%;
            max-width: 500px;
        }
        
        .verify-box {
            background: white;
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .verify-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .verify-icon.success {
            color: #4CAF50;
        }
        
        .verify-icon.error {
            color: #F44336;
        }
        
        .verify-box h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2D5016;
            margin-bottom: 15px;
        }
        
        .verify-box p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .bonus-badge {
            display: inline-block;
            background: #F0F8EE;
            color: #6BBF59;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 700;
            margin: 20px 0;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 16px 40px;
            background: #6BBF59;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: #5BAE49;
            transform: scale(1.05);
        }
        
        .features-list {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #F9F9F9;
            border-radius: 8px;
        }
        
        .features-list h3 {
            font-size: 16px;
            font-weight: 700;
            color: #2D5016;
            margin-bottom: 12px;
            text-align: center;
        }
        
        .features-list ul {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .features-list li i {
            color: #6BBF59;
            margin-right: 10px;
            width: 20px;
        }
        
        @media (max-width: 500px) {
            .verify-box {
                padding: 40px 25px;
            }
            
            .verify-icon {
                font-size: 60px;
            }
            
            .verify-box h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-box">
            <?php if ($result && $result['success']): ?>
                <!-- Успешная верификация -->
                <div class="verify-icon success">✅</div>
                <h1>Email подтвержден!</h1>
                <p>Ваш аккаунт успешно активирован.</p>
                
                <div class="bonus-badge">
                    <i class="fas fa-gift"></i> +100 бонусов на счёт!
                </div>
                
                <div class="features-list">
                    <h3>Что вас ждет:</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Кешбэк до 7% с каждой покупки</li>
                        <li><i class="fas fa-check-circle"></i> Колесо фортуны каждый день</li>
                        <li><i class="fas fa-check-circle"></i> Участие в эко-программе</li>
                        <li><i class="fas fa-check-circle"></i> Персональные скидки</li>
                        <li><i class="fas fa-check-circle"></i> Быстрая доставка</li>
                    </ul>
                </div>
                
                <p>Теперь вы можете войти в систему и начать делать покупки!</p>
                
                <a href="<?php echo SITE_URL; ?>/customer/dashboard.php" class="btn-primary">
                    Войти в аккаунт
                </a>
                
            <?php elseif ($result && !$result['success']): ?>
                <!-- Ошибка верификации -->
                <div class="verify-icon error">❌</div>
                <h1>Ошибка подтверждения</h1>
                <p><?php echo e($result['error']); ?></p>
                <p>Возможные причины:</p>
                <ul style="text-align: left; color: #666; font-size: 14px; margin: 20px 0;">
                    <li>Ссылка устарела (действительна 24 часа)</li>
                    <li>Email уже подтвержден</li>
                    <li>Неверная ссылка подтверждения</li>
                </ul>
                
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn-primary">
                    Регистрация
                </a>
                <br>
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn-primary" style="background: white; color: #6BBF59; border: 2px solid #6BBF59; margin-top: 10px;">
                    Вход
                </a>
                
            <?php else: ?>
                <!-- Нет токена -->
                <div class="verify-icon error">⚠️</div>
                <h1>Неверная ссылка</h1>
                <p>Ссылка для подтверждения email не найдена.</p>
                <p>Пожалуйста, проверьте ссылку в письме или запросите новое письмо.</p>
                
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn-primary">
                    Регистрация
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>