<?php
/**
 * ═══════════════════════════════════════════════════════════
 * GOOGLE OAUTH CALLBACK
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Проверка кода авторизации
if (!isset($_GET['code'])) {
    setFlash('error', 'Ошибка авторизации через Google');
    redirect('/login.php');
    exit;
}

// ОПРЕДЕЛИТЬ ОТКУДА ПРИШЁЛ ПОЛЬЗОВАТЕЛЬ через параметр state
$action = isset($_GET['state']) ? $_GET['state'] : 'login';

$code = $_GET['code'];

// Обмен кода на токен
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$token_response = curl_exec($ch);
curl_close($ch);

$token_info = json_decode($token_response, true);

if (!isset($token_info['access_token'])) {
    setFlash('error', 'Не удалось получить токен от Google');
    redirect('/login.php');
    exit;
}

// Получение информации о пользователе
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($user_info_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token_info['access_token']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$google_user = json_decode($user_response, true);

if (!isset($google_user['email'])) {
    setFlash('error', 'Не удалось получить email от Google');
    redirect('/login.php');
    exit;
}

// Проверить существование пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$google_user['email']]);
$existing_user = $stmt->fetch();

try {
    if ($existing_user) {
        // ПОЛЬЗОВАТЕЛЬ УЖЕ СУЩЕСТВУЕТ
        
        if ($action === 'register') {
            // Попытка РЕГИСТРАЦИИ с существующим email - ОШИБКА
            $_SESSION['google_error'] = 'Аккаунт с email <strong>' . e($google_user['email']) . '</strong> уже зарегистрирован. Для входа используйте страницу "Вход" и кнопку "Войти через Google".';
            redirect('/register.php');
            exit;
        } else {
            // ВХОД существующего пользователя
            
            // Проверить статус аккаунта
            if ($existing_user['status'] === 'pending') {
                $_SESSION['login_error'] = 'Пожалуйста, подтвердите ваш email адрес. Проверьте почту.';
                header("Location: " . SITE_URL . "/login.php");
                exit;
            }
            
            if ($existing_user['status'] === 'blocked') {
                setFlash('error', 'Ваш аккаунт заблокирован. Обратитесь в поддержку.');
                redirect('/login.php');
                exit;
            }
            
            // ВХОД - ОК
            $_SESSION['user_id'] = $existing_user['id'];
            $_SESSION['user_role'] = $existing_user['role'];
            
            // Обновить последний вход
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$existing_user['id']]);
            
            setFlash('success', 'С возвращением, ' . e($existing_user['first_name']) . '!');
            redirect('/index.php');
        }
        
    } else {
        // НОВЫЙ ПОЛЬЗОВАТЕЛЬ
        
        if ($action === 'login') {
            // Попытка ВХОДА несуществующего пользователя
            setFlash('error', 'Аккаунт с этим email не найден. Пожалуйста, зарегистрируйтесь.');
            redirect('/register.php');
            exit;
        }
        
        // РЕГИСТРАЦИЯ нового пользователя со статусом PENDING
        $pdo->beginTransaction();
        
        // Разделить имя на first_name и last_name
        $name_parts = explode(' ', $google_user['name'], 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';
        
        // Создать пользователя со статусом PENDING (требуется подтверждение email)
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, status, role)
            VALUES (?, ?, ?, ?, 'pending', 'customer')
        ");
        
        $random_hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        
        $stmt->execute([
            $google_user['email'],
            $random_hash,
            $first_name,
            $last_name
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Генерация токена верификации
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $pdo->prepare("
            INSERT INTO email_verifications (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $token, $expires_at]);
        
        // ОТПРАВИТЬ ПИСЬМО (используем существующую функцию из includes/email.php)
        require_once __DIR__ . '/includes/email.php';
        $verification_link = SITE_URL . "/verify-email.php?token=" . $token;
        $subject = "Подтвердите Email - Райский уголок";
        $message = getEmailTemplate([
            'title' => 'Подтверждение регистрации',
            'greeting' => "Здравствуйте, {$first_name}!",
            'content' => "
                <p>Вы зарегистрировались через Google в <strong>Райский уголок</strong>.</p>
                <p>Для завершения регистрации подтвердите ваш email:</p>
            ",
            'button_text' => 'Подтвердить Email',
            'button_url' => $verification_link,
            'footer_text' => "<p>Ссылка действительна 24 часа.</p>"
        ]);
        sendEmail($google_user['email'], $subject, $message);
        
        $pdo->commit();
        
        // Установить флаг успешной регистрации для register.php
        $_SESSION['registration_success'] = true;
        $_SESSION['registration_email'] = $google_user['email'];
        
        // Перенаправить на register.php, которая покажет сообщение об успехе
        redirect('/register.php');
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Google OAuth Error: " . $e->getMessage());
    setFlash('error', 'Ошибка при авторизации. Попробуйте позже.');
    redirect('/login.php');
}

/**
 * Генерация номера карты лояльности
 */
function generateLoyaltyCardNumber() {
    $parts = [];
    for ($i = 0; $i < 4; $i++) {
        $parts[] = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    return implode('-', $parts);
}
?>