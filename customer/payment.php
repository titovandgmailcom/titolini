<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА ОПЛАТЫ ЮKASSA
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payment.php';

requireRole('customer');

$order_id = $_GET['order_id'] ?? null;

if (!$order_id || !isset($_SESSION['pending_order'])) {
    setFlash('error', 'Заказ не найден');
    redirect('/customer/orders.php');
}

$pending_order = $_SESSION['pending_order'];

if ($pending_order['order_id'] != $order_id) {
    setFlash('error', 'Неверный идентификатор заказа');
    redirect('/customer/orders.php');
}

$user = getCurrentUser();

// Создать платёж в ЮKassa
$return_url = SITE_URL . '/customer/payment-check.php?order_id=' . $order_id;

$payment_result = createYooKassaPayment(
    $order_id,
    $pending_order['amount_to_pay'],
    $pending_order['order_number'],
    $return_url
);

if (!$payment_result['success']) {
    setFlash('error', 'Ошибка создания платежа. Попробуйте позже.');
    redirect('/customer/orders.php');
}

// Сохранить ID платежа в сессию
$_SESSION['payment_id'] = $payment_result['payment_id'];

// Перенаправить на страницу оплаты ЮKassa
header('Location: ' . $payment_result['confirmation_url']);
exit;