<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ВЫХОД ИЗ СИСТЕМЫ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';

// Сохранить информацию о пользователе для логирования (опционально)
$user_id = $_SESSION['user_id'] ?? null;

// Удалить все данные сессии
$_SESSION = array();

// Удалить cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Удалить cookie "Запомнить меня" если есть
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Удалить токен из базы данных
    if ($user_id) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Logout Error: " . $e->getMessage());
        }
    }
}

// Уничтожить сессию
session_destroy();

// Перенаправить на главную страницу
header("Location: " . SITE_URL . "/index.php");
exit;
?>