<?php
/**
 * API ИЗБРАННОГО
 * Файл должен быть в папке /rayskiy-ugolok/api/favorites.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];

// GET запрос - получить избранное
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $favorites = getFavorites($user_id);
    $count = getFavoritesCount($user_id);
    
    echo json_encode([
        'success' => true,
        'items' => $favorites,
        'count' => $count
    ]);
    exit;
}

// POST запрос - добавить/удалить
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? '';
    $product_id = $input['product_id'] ?? 0;
    
    if (!$action || !$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
        exit;
    }
    
    switch ($action) {
        case 'add':
            $result = addToFavorites($user_id, $product_id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар добавлен в избранное',
                    'count' => getFavoritesCount($user_id)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении']);
            }
            exit;
            
        case 'remove':
            $result = removeFromFavorites($user_id, $product_id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар удален из избранного',
                    'count' => getFavoritesCount($user_id)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при удалении']);
            }
            exit;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
            exit;
    }
}

// Неподдерживаемый метод
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
exit;