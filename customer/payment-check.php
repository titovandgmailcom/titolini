<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payment.php';
require_once __DIR__ . '/../includes/functions.php';  // ДОБАВИТЬ ЭТУ СТРОКУ!

requireRole('customer');

$order_id = $_GET['order_id'] ?? null;
$payment_id = $_SESSION['payment_id'] ?? null;

error_log("=== PAYMENT CHECK STARTED ===");
error_log("Order ID: $order_id, Payment ID: $payment_id");

if (!$order_id || !$payment_id) {
    error_log("ERROR: Missing order_id or payment_id");
    setFlash('error', 'Данные платежа не найдены');
    redirect('/customer/orders.php');
    exit;
}

$payment_info = checkYooKassaPayment($payment_id);

error_log("Payment status from YooKassa: " . ($payment_info['status'] ?? 'unknown'));

if ($payment_info['status'] === 'succeeded') {
    error_log("Payment succeeded, calling handlePaymentSuccess...");
    
    $result = handlePaymentSuccess(
        $order_id, 
        $payment_id, 
        floatval($payment_info['amount']['value'])
    );
    
    if ($result['success']) {
        error_log("✓ handlePaymentSuccess completed successfully");
        
        unset($_SESSION['pending_order']);
        unset($_SESSION['payment_id']);
        
        setFlash('success', 'Оплата прошла успешно!');
        redirect('/customer/order-success.php?order_id=' . $order_id);
    } else {
        error_log("✗ handlePaymentSuccess FAILED: " . $result['error']);
        setFlash('error', 'Ошибка обработки платежа: ' . $result['error']);
        redirect('/customer/orders.php');
    }
    exit;
    
} elseif ($payment_info['status'] === 'canceled') {
    error_log("Payment canceled");
    handlePaymentFailed($order_id, 'Платёж отменён');
    unset($_SESSION['payment_id']);
    
    setFlash('error', 'Оплата не прошла.');
    redirect('/customer/checkout.php');
    exit;
    
} else {
    error_log("Payment still processing, status: " . $payment_info['status']);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Обработка платежа</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f5f5;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .container {
                text-align: center;
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #6BBF59;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
        <meta http-equiv="refresh" content="3">
    </head>
    <body>
        <div class="container">
            <div class="spinner"></div>
            <h2>Обработка платежа...</h2>
            <p>Пожалуйста, подождите</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}