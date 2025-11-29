<?php
/**
 * API КОРЗИНЫ
 * Файл должен быть в папке /rayskiy-ugolok/api/cart.php
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

// GET запрос - получить количество товаров
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'count') {
    $count = getCartCount($user_id);
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// GET запрос - получить корзину
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cart = getCart($user_id);
    $total = getCartTotal($user_id);
    
    echo json_encode([
        'success' => true,
        'items' => $cart,
        'total' => $total,
        'count' => getCartCount($user_id)
    ]);
    exit;
}

// POST запрос - добавить/обновить/удалить
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? '';
    $product_id = $input['product_id'] ?? 0;
    $quantity = $input['quantity'] ?? 1;
    
    if (!$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Действие не указано']);
        exit;
    }
    
    switch ($action) {
        case 'add':
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID товара не указан']);
                exit;
            }
            
            $result = addToCart($user_id, $product_id, $quantity);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар добавлен в корзину',
                    'count' => getCartCount($user_id)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении товара']);
            }
            exit;
            
        case 'update':
            if (!$product_id || $quantity < 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
                exit;
            }
            
            $result = updateCartQuantity($user_id, $product_id, $quantity);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Количество обновлено',
                    'count' => getCartCount($user_id),
                    'total' => getCartTotal($user_id)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при обновлении']);
            }
            exit;
            
        case 'remove':
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID товара не указан']);
                exit;
            }
            
            $result = removeFromCart($user_id, $product_id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар удален из корзины',
                    'count' => getCartCount($user_id),
                    'total' => getCartTotal($user_id)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при удалении']);
            }
            exit;
            
        case 'clear':
            $result = clearCart($user_id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Корзина очищена'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при очистке корзины']);
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