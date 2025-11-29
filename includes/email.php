<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СИСТЕМА ОТПРАВКИ EMAIL
 * Интернет-магазин "Райский уголок"
 * Отправка через Timeweb SMTP с PHPMailer
 * ═══════════════════════════════════════════════════════════
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/settings.php';

// Подключить PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // Если установлен через Composer
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/PHPMailer-6.11.1/src/PHPMailer.php')) {
    // Если установлен вручную в includes/PHPMailer-6.11.1/
    require_once __DIR__ . '/PHPMailer-6.11.1/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-6.11.1/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-6.11.1/src/SMTP.php';
}

/**
 * Базовая функция отправки email через SMTP
 */
function sendEmail($to, $subject, $html_body, $text_body = '') {
    // Проверка, подключен ли PHPMailer
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Using fallback mail() function.");
        return sendEmailFallback($to, $subject, $html_body);
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Настройки SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 25; // ← ИЗМЕНЕНО на порт 25
        $mail->CharSet = 'UTF-8';
        
        // Отключить проверку сертификата (для некоторых хостингов)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Отправитель и получатель
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Содержимое письма
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = $text_body ?: strip_tags($html_body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        // Попробовать отправить через стандартную функцию mail()
        return sendEmailFallback($to, $subject, $html_body);
    }
}

/**
 * Резервная функция отправки через mail()
 */
function sendEmailFallback($to, $subject, $html_body) {
    $headers = [
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'Reply-To: ' . SMTP_FROM_EMAIL,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $success = mail($to, $subject, $html_body, implode("\r\n", $headers));
    
    if (!$success) {
        error_log("Fallback mail() also failed to: $to");
    }
    
    return $success;
}

/**
 * Отправка email верификации
 */
function sendVerificationEmail($email, $token, $first_name) {
    $verify_url = SITE_URL . '/verify-email.php?token=' . $token;
    
    $subject = 'Подтверждение email - ' . SITE_NAME;
    
    $html = getEmailTemplate([
        'title' => 'Подтверждение регистрации',
        'greeting' => "Здравствуйте, $first_name!",
        'content' => "
            <p>Спасибо за регистрацию в интернет-магазине <strong>Райский уголок</strong>!</p>
            <p>Для завершения регистрации и активации вашего аккаунта, пожалуйста, подтвердите email адрес, нажав на кнопку ниже:</p>
        ",
        'button_text' => 'Подтвердить email',
        'button_url' => $verify_url,
        'footer_text' => "
            <p>После подтверждения вам будет начислено <strong>100 бонусов</strong> на вашу карту лояльности!</p>
            <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>
            <p><small>Ссылка действительна в течение 24 часов.</small></p>
        "
    ]);
    
    return sendEmail($email, $subject, $html);
}

/**
 * Отправка приветственного email
 */
function sendWelcomeEmail($email, $first_name) {
    $subject = 'Добро пожаловать в ' . SITE_NAME . '!';
    
    $html = getEmailTemplate([
        'title' => 'Добро пожаловать!',
        'greeting' => "Здравствуйте, $first_name!",
        'content' => "
            <p>Ваш аккаунт успешно активирован! Мы рады приветствовать вас в нашем интернет-магазине.</p>
            <p>На вашу карту лояльности уже начислено <strong>100 бонусов</strong>, которые вы можете использовать при первом заказе!</p>
            <h3 style='color: #6BBF59; margin-top: 20px;'>Что вас ждет:</h3>
            <ul style='line-height: 1.8;'>
                <li><strong>Свежие продукты</strong> от проверенных поставщиков</li>
                <li><strong>Программа лояльности</strong> с кешбэком до 7%</li>
                <li><strong>Колесо фортуны</strong> - крутите каждый день и выигрывайте призы!</li>
                <li><strong>Эко-программа</strong> - сканируйте QR-коды и получайте бонусы</li>
                <li><strong>Быстрая доставка</strong> в удобное время</li>
            </ul>
        ",
        'button_text' => 'Начать покупки',
        'button_url' => SITE_URL . '/catalog.php',
        'footer_text' => "
            <p>Спасибо, что выбрали нас! Приятных покупок!</p>
        "
    ]);
    
    return sendEmail($email, $subject, $html);
}

/**
 * Отправка email для сброса пароля
 */
function sendPasswordResetEmail($email, $token, $first_name) {
    $reset_url = SITE_URL . '/reset-password.php?token=' . $token;
    
    $subject = 'Сброс пароля - ' . SITE_NAME;
    
    $html = getEmailTemplate([
        'title' => 'Сброс пароля',
        'greeting' => "Здравствуйте, $first_name!",
        'content' => "
            <p>Мы получили запрос на сброс пароля для вашего аккаунта.</p>
            <p>Для установки нового пароля нажмите на кнопку ниже:</p>
        ",
        'button_text' => 'Сбросить пароль',
        'button_url' => $reset_url,
        'footer_text' => "
            <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо. Ваш пароль останется без изменений.</p>
            <p><small>Ссылка действительна в течение 1 часа.</small></p>
        "
    ]);
    
    return sendEmail($email, $subject, $html);
}

/**
 * Отправка уведомления о новом заказе
 */
function sendOrderConfirmationEmail($email, $first_name, $order_number, $total_amount) {
    $subject = 'Заказ №' . $order_number . ' оформлен - ' . SITE_NAME;
    
    $html = getEmailTemplate([
        'title' => 'Заказ оформлен!',
        'greeting' => "Здравствуйте, $first_name!",
        'content' => "
            <p>Ваш заказ <strong>№$order_number</strong> успешно оформлен!</p>
            <div style='background: #F0F8EE; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 0; font-size: 14px; color: #666;'>Сумма заказа:</p>
                <p style='margin: 5px 0 0 0; font-size: 24px; color: #6BBF59; font-weight: 700;'>" . formatPrice($total_amount) . "</p>
            </div>
            <p>Мы уже начали обрабатывать ваш заказ. Вы получите уведомление, когда заказ будет готов к доставке.</p>
        ",
        'button_text' => 'Отследить заказ',
        'button_url' => SITE_URL . '/customer/orders.php',
        'footer_text' => "
            <p>Спасибо за покупку!</p>
        "
    ]);
    
    return sendEmail($email, $subject, $html);
}

/**
 * Базовый шаблон email
 */
function getEmailTemplate($data) {
    $title = $data['title'] ?? '';
    $greeting = $data['greeting'] ?? '';
    $content = $data['content'] ?? '';
    $button_text = $data['button_text'] ?? null;
    $button_url = $data['button_url'] ?? '#';
    $footer_text = $data['footer_text'] ?? '';
    
    $button_html = '';
    if ($button_text) {
        $button_html = "
        <table width='100%' cellpadding='0' cellspacing='0' style='margin: 30px 0;'>
            <tr>
                <td align='center'>
                    <a href='$button_url' style='display: inline-block; padding: 16px 40px; background: #6BBF59; color: white; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px;'>$button_text</a>
                </td>
            </tr>
        </table>
        ";
    }
    
    return "
    <!DOCTYPE html>
    <html lang='ru'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background: #F5F5F5;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background: #F5F5F5; padding: 40px 20px;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
                        <!-- Header -->
                        <tr>
                            <td style='background: linear-gradient(135deg, #6BBF59 0%, #5BAE49 100%); padding: 30px; text-align: center; border-radius: 16px 16px 0 0;'>
                                <h1 style='margin: 0; color: white; font-size: 28px; font-weight: 700;'>Райский уголок</h1>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style='padding: 40px;'>
                                <h2 style='margin: 0 0 20px 0; color: #2D5016; font-size: 24px;'>$title</h2>
                                <p style='margin: 0 0 20px 0; color: #333; font-size: 16px; line-height: 1.6;'>$greeting</p>
                                <div style='color: #333; font-size: 15px; line-height: 1.6;'>
                                    $content
                                </div>
                                $button_html
                                <div style='color: #666; font-size: 14px; line-height: 1.6; margin-top: 20px;'>
                                    $footer_text
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background: #F0F8EE; padding: 30px; text-align: center; border-radius: 0 0 16px 16px;'>
                                <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>
                                    С уважением, команда <strong>Райский уголок</strong>
                                </p>
                                <p style='margin: 0; color: #999; font-size: 12px;'>
                                    Москва, Россия | info@rayskiy-ugolok.ru | +7 (800) 555-35-35
                                </p>
                                <p style='margin: 10px 0 0 0; color: #999; font-size: 12px;'>
                                    <a href='" . SITE_URL . "' style='color: #6BBF59; text-decoration: none;'>Посетить сайт</a> | 
                                    <a href='" . SITE_URL . "/customer/profile.php' style='color: #6BBF59; text-decoration: none;'>Мой профиль</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";
}