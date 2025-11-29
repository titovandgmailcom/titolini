<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СИСТЕМА АВТОРИЗАЦИИ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';

/**
 * Регистрация нового пользователя
 */
function registerUser($email, $password, $first_name, $last_name, $phone = null) {
    global $pdo;
    
    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Некорректный email адрес'];
    }
    
    // Валидация пароля
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов'];
    }
    
    // Проверка существования email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Пользователь с таким email уже существует'];
    }

        // ДОБАВИТЬ: Проверка существования телефона (если указан)
    if (!empty($phone)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Пользователь с таким номером телефона уже зарегистрирован'];
        }
    }

    
    // Хеширование пароля
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $pdo->beginTransaction();
        
        // Создание пользователя
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, role, first_name, last_name, phone, status)
            VALUES (?, ?, 'customer', ?, ?, ?, 'pending')
        ");
        $stmt->execute([$email, $password_hash, $first_name, $last_name, $phone]);
        $user_id = $pdo->lastInsertId();
        
        // Генерация токена верификации
        $token = generateToken();
        $expires_at = date('Y-m-d H:i:s', strtotime('+' . TOKEN_EXPIRY_HOURS . ' hours'));
        
        $stmt = $pdo->prepare("
            INSERT INTO email_verifications (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $token, $expires_at]);
        
        $pdo->commit();
        
        // Отправка email верификации
        require_once __DIR__ . '/email.php';
        sendVerificationEmail($email, $token, $first_name);
        
        return [
            'success' => true,
            'message' => 'Регистрация успешна! Проверьте email для подтверждения аккаунта',
            'user_id' => $user_id
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Registration Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при регистрации. Попробуйте позже'];
    }
}

/**
 * Авторизация пользователя
 */
function login($email, $password, $remember = false) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'error' => 'Неверный email или пароль'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Неверный email или пароль'];
    }
    
    if ($user['status'] === 'pending') {
        return ['success' => false, 'error' => 'Пожалуйста, подтвердите email адрес'];
    }
    
    if ($user['status'] === 'blocked') {
        return ['success' => false, 'error' => 'Ваш аккаунт заблокирован'];
    }
    
    // Установка сессии
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    
    // Обновление времени последнего входа
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/');
    }
    
    return [
        'success' => true,
        'user' => $user,
        'redirect' => getRedirectByRole($user['role'])
    ];
}

/**
 * Выход пользователя
 */
function logout() {
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    session_destroy();
    redirect('/index.php');
}

/**
 * Проверка авторизации
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Получение текущего пользователя
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Проверка роли пользователя
 */
function checkRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($required_role)) {
        return in_array($_SESSION['user_role'], $required_role);
    }
    
    return $_SESSION['user_role'] === $required_role;
}

/**
 * Требовать авторизацию
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Для доступа к этой странице необходимо войти в систему');
        redirect('/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Требовать определенную роль
 */
function requireRole($required_role) {
    requireLogin();
    
    if (!checkRole($required_role)) {
        setFlash('error', 'У вас нет доступа к этой странице');
        redirect(getRedirectByRole($_SESSION['user_role']));
    }
}

/**
 * Получить путь редиректа по роли
 */
function getRedirectByRole($role) {
    $routes = [
        'customer' => '/customer/dashboard.php',
        'director' => '/director/dashboard.php',
        'admin' => '/admin/dashboard.php',
        'manager' => '/manager/dashboard.php',
        'supplier' => '/supplier/dashboard.php',
        'courier' => '/courier/dashboard.php'
    ];
    
    return $routes[$role] ?? '/index.php';
}

/**
 * Генерация безопасного токена
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Верификация email
 */
function verifyEmail($token) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT ev.*, u.id as user_id, u.email, u.first_name
            FROM email_verifications ev
            JOIN users u ON ev.user_id = u.id
            WHERE ev.token = ? AND ev.expires_at > NOW() AND ev.verified_at IS NULL
        ");
        $stmt->execute([$token]);
        $verification = $stmt->fetch();
        
        if (!$verification) {
            return ['success' => false, 'error' => 'Недействительный или истекший токен'];
        }
        
        $pdo->beginTransaction();
        
        // Активация пользователя
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$verification['user_id']]);
        
        // Отметка о верификации
        $stmt = $pdo->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE id = ?");
        $stmt->execute([$verification['id']]);
        
        // Проверить существование карты лояльности
        $stmt = $pdo->prepare("SELECT id FROM loyalty_cards WHERE user_id = ?");
        $stmt->execute([$verification['user_id']]);
        $existing_card = $stmt->fetch();
        
        // Создать карту лояльности только если её нет
        if (!$existing_card) {
            $card_number = generateCardNumber();
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_cards (user_id, card_number, points_balance)
                VALUES (?, ?, 100.00)
            ");
            $stmt->execute([$verification['user_id'], $card_number]);
            
            // Запись транзакции начисления бонусов
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (user_id, type, amount, description)
                VALUES (?, 'bonus', 100.00, 'Бонус за регистрацию')
            ");
            $stmt->execute([$verification['user_id']]);
        }
        
        $pdo->commit();
        
        // АВТОМАТИЧЕСКАЯ АВТОРИЗАЦИЯ ПОСЛЕ ПОДТВЕРЖДЕНИЯ
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$verification['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Обновить время последнего входа
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        
        // Отправка приветственного email
        require_once __DIR__ . '/email.php';
        sendWelcomeEmail($verification['email'], $verification['first_name']);
        
        return [
            'success' => true,
            'message' => 'Email успешно подтвержден! Вам начислено 100 бонусов',
            'user_id' => $verification['user_id'],
            'auto_login' => true
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Email Verification Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при подтверждении email'];
    }
}


/**
 * Генерация номера карты лояльности
 */
function generateCardNumber() {
    return sprintf(
        '%04d-%04d-%04d-%04d',
        rand(1000, 9999),
        rand(1000, 9999),
        rand(1000, 9999),
        rand(1000, 9999)
    );
}

/**
 * Сброс пароля - запрос
 */
function requestPasswordReset($email) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => true, 'message' => 'Если email существует, на него отправлена инструкция'];
    }
    
    $token = generateToken();
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $pdo->prepare("
        INSERT INTO email_verifications (user_id, token, expires_at)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user['id'], $token, $expires_at]);
    
    require_once __DIR__ . '/email.php';
    sendPasswordResetEmail($email, $token, $user['first_name']);
    
    return ['success' => true, 'message' => 'Инструкция по сбросу пароля отправлена на email'];
}

/**
 * Сброс пароля - установка нового
 */
function resetPassword($token, $new_password) {
    global $pdo;
    
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Пароль слишком короткий'];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT user_id FROM email_verifications
            WHERE token = ? AND expires_at > NOW() AND verified_at IS NULL
        ");
        $stmt->execute([$token]);
        $verification = $stmt->fetch();
        
        if (!$verification) {
            return ['success' => false, 'error' => 'Недействительный или истекший токен'];
        }
        
        $pdo->beginTransaction();
        
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $verification['user_id']]);
        
        $stmt = $pdo->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE token = ?");
        $stmt->execute([$token]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Пароль успешно изменен'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Password Reset Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при сбросе пароля'];
    }
}

/**
 * Изменение пароля (для авторизованного пользователя)
 */
function changePassword($user_id, $old_password, $new_password) {
    global $pdo;
    
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Новый пароль слишком короткий'];
    }
    
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($old_password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Неверный текущий пароль'];
    }
    
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$password_hash, $user_id]);
    
    return ['success' => true, 'message' => 'Пароль успешно изменен'];
}