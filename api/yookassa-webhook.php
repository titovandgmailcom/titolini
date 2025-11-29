<?php
/**
 * ═══════════════════════════════════════════════════════════
 * WEBHOOK ДЛЯ УВЕДОМЛЕНИЙ ЮKASSA
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/payment.php';

// Получить данные от ЮKassa
$source = file_get_contents('php://input');
$data = json_decode($source, true);

// Логирование для отладки
error_log("YooKassa Webhook: " . $source);

if (!$data || !isset($data['event'])) {
    http_response_code(400);
    exit;
}

$event = $data['event'];
$payment = $data['object'];

$order_id = $payment['metadata']['order_id'] ?? null;
$payment_id = $payment['id'] ?? null;

if (!$order_id || !$payment_id) {
    http_response_code(400);
    exit;
}

switch ($event) {
    case 'payment.succeeded':
        handlePaymentSuccess(
            $order_id, 
            $payment_id, 
            floatval($payment['amount']['value'])
        );
        break;
        
    case 'payment.canceled':
        handlePaymentFailed($order_id, 'Платёж отменён');
        break;
        
    case 'refund.succeeded':
        // Обработка возврата (если нужно)
        break;
}

http_response_code(200);