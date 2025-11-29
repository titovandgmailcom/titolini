<?php
/**
 * ═══════════════════════════════════════════════════════════
 * НАСТРОЙКИ СИСТЕМЫ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Основные настройки
define('SITE_NAME', 'Райский уголок');
define('SITE_URL', 'https://cz01249.tw1.ru');
define('ADMIN_EMAIL', 'admin@rayskiy-ugolok.ru');

// Настройки безопасности
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 3600 * 24); // 24 часа
define('TOKEN_EXPIRY_HOURS', 24);

// Настройки email (Timeweb SMTP)
define('SMTP_HOST', 'smtp.timeweb.ru');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'rayskiy-ugolok@cz01249.tw1.ru');
define('SMTP_PASSWORD', 'Roadwings1823');
define('SMTP_FROM_EMAIL', 'rayskiy-ugolok@cz01249.tw1.ru');
define('SMTP_FROM_NAME', 'Райский уголок');

// Настройки системы лояльности
define('LOYALTY_LEVELS', [
    'bronze' => [
        'name' => 'Бронза',
        'min_spent' => 0,
        'max_spent' => 10000,
        'cashback_percent' => 1,
        'daily_spins' => 1,
        'color' => 'bronze',
        'gradient' => 'linear-gradient(135deg, #8B4513 0%, #CD853F 50%, #DAA520 100%)'
    ],
    'silver' => [
        'name' => 'Серебро',
        'min_spent' => 10001,
        'max_spent' => 30000,
        'cashback_percent' => 3,
        'daily_spins' => 1,
        'color' => 'silver',
        'gradient' => 'linear-gradient(135deg, #A8B8C8 0%, #C0C0C0 50%, #87CEEB 100%)'
    ],
    'gold' => [
        'name' => 'Золото',
        'min_spent' => 30001,
        'max_spent' => 70000,
        'cashback_percent' => 5,
        'daily_spins' => 2,
        'color' => 'gold',
        'gradient' => 'linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%)'
    ],
    'platinum' => [
        'name' => 'Платина',
        'min_spent' => 70001,
        'max_spent' => PHP_INT_MAX,
        'cashback_percent' => 7,
        'daily_spins' => 3,
        'color' => 'platinum',
        'gradient' => 'linear-gradient(135deg, #E5E4E2 0%, #C9C0DE 50%, #9370DB 100%)'
    ]
]);

// Настройки доставки
define('FREE_DELIVERY_THRESHOLD', [
    'bronze' => 5000,
    'silver' => 3000,
    'gold' => 2000,
    'platinum' => 0
]);

define('DELIVERY_COST', 250);

// Настройки каталога
define('PRODUCTS_PER_PAGE', 24);
define('FEATURED_PRODUCTS_COUNT', 12);

// Пути к файлам
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
define('PRODUCT_IMAGE_DIR', __DIR__ . '/../assets/images/products/');
define('CATEGORY_IMAGE_DIR', __DIR__ . '/../assets/images/categories/');
define('USER_AVATAR_DIR', __DIR__ . '/../assets/images/avatars/');

// Разрешенные типы файлов
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB

// Настройки отображения ошибок (выключить на production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Локаль
setlocale(LC_ALL, 'ru_RU.UTF-8');

// Google OAuth настройки
define('GOOGLE_CLIENT_ID', '583815875043-c0j92qblnm5acrdo48mj74g92u9eskub.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/google-callback.php');

// ═══════════════════════════════════════════════════════════
// ЭКО-ПРОГРАММА
// ═══════════════════════════════════════════════════════════

define('ECO_BADGES', [
    'novice' => [
        'name' => 'Эко-новичок',
        'qr_count' => 10,
        'bonus' => 100,
        'icon' => '🌱',
        'color' => '#8B4513'
    ],
    'protector' => [
        'name' => 'Защитник природы',
        'qr_count' => 50,
        'bonus' => 250,
        'icon' => '🌳',
        'color' => '#C0C0C0'
    ],
    'hero' => [
        'name' => 'Эко-герой',
        'qr_count' => 100,
        'bonus' => 500,
        'icon' => '🌍',
        'color' => '#FFD700'
    ],
    'champion' => [
        'name' => 'Зелёный чемпион',
        'qr_count' => 250,
        'bonus' => 750,
        'icon' => '👑',
        'color' => '#50C878'
    ],
    'guardian' => [
        'name' => 'Планета в безопасности',
        'qr_count' => 500,
        'bonus' => 1000,
        'icon' => '🛡️',
        'color' => '#4169E1'
    ],
    'legend' => [
        'name' => 'Эко-легенда',
        'qr_count' => 1000,
        'bonus' => 1500,
        'icon' => '🔥',
        'color' => '#FF1493'
    ],
    'savior' => [
        'name' => 'Спаситель Земли',
        'qr_count' => 2000,
        'bonus' => 2000,
        'icon' => '✨',
        'color' => '#9370DB'
    ]
]);

define('ECO_POINTS_PER_QR', 5);
define('SECRET_KEY', 'rayskiy_ugolok_secret_' . md5('paradise_corner_2025_eco_program'));



// ═══════════════════════════════════════════════════════════
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ═══════════════════════════════════════════════════════════

/**
 * Функция для получения настроек уровня лояльности
 */
function getLoyaltyLevelSettings($total_spent) {
    foreach (LOYALTY_LEVELS as $level => $settings) {
        if ($total_spent >= $settings['min_spent'] && $total_spent <= $settings['max_spent']) {
            return array_merge(['level' => $level], $settings);
        }
    }
    return array_merge(['level' => 'bronze'], LOYALTY_LEVELS['bronze']);
}

/**
 * Функция для форматирования цены
 */
function formatPrice($price) {
    return number_format($price, 2, '.', ' ') . ' ₽';
}

/**
 * Функция для форматирования даты
 */
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

/**
 * Функция для форматирования даты и времени
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    return date($format, strtotime($datetime));
}

/**
 * Функция для генерации slug из строки
 */
function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    
    $transliteration = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $string = strtr($string, $transliteration);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Функция для безопасного вывода HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Функция для редиректа
 */
function redirect($url) {
    if (headers_sent()) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        echo "<script>window.location.href = '" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES) . "'></noscript>";
        exit;
    }
    
    header("Location: $url");
    exit;
}

/**
 * Функция для проверки AJAX запроса
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Функция для отправки JSON ответа
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Функция для установки flash сообщения
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Функция для получения и удаления flash сообщения
 */
function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Функция для проверки наличия flash сообщения
 */
function hasFlash($type) {
    return isset($_SESSION['flash'][$type]);
}