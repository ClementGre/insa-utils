<?php

require __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function send_auth_mail($name, $email_prefix, $id, $email_token, $email_code, $redirect): void
{

    require_once __DIR__ . '/../mailing/auth_content.php';

    $url = "https://utils.bde-insa-lyon.fr/account/login?id=" . $id . '&token=' . $email_token . '&redirect=' . $redirect;
    $unsubscribe_url = "https://utils.bde-insa-lyon.fr/account/unsubscribe?&id=" . $id . '&token=' . $email_token;

    $text = get_auth_mail_text_content($url, $email_code, $unsubscribe_url);
    $html = get_auth_mail_content($url, $email_code, $unsubscribe_url);

    $subject = 'Authentification sur INSA Utils';

    sendMail($name, $email_prefix . '@insa-lyon.fr', $subject, $html, $text);
}
function send_disable_email_mail($name, $email_prefix, $id, $email_resubscribe_token): void
{

    require_once __DIR__ . '/../mailing/disable_email_content.php';

    $url = "https://utils.bde-insa-lyon.fr/account/unsubscribe?id=" . $id . '&resubscribe_token=' . $email_resubscribe_token;

    $text = get_disable_email_mail_text_content($url);
    $html = get_disable_email_mail_content($url);

    $subject = 'Désactivation de la réception d\'emails d\'insa-utils';

    sendMail($name, $email_prefix . '@insa-lyon.fr', $subject, $html, $text);
}

/**
 * @param $to : receiver email address
 * @param $subject : email subject
 * @param $htmlBody : email body in html
 * @param $noHtmlBody : email body if html is not supported
 * @return false|string : false = OK | string = error message
 */
function sendMail($toName, $to, $subject, $htmlBody, $noHtmlBody): false|string
{
    error_log("Sending email to $to with subject $subject");

    $mail = new PHPMailer(true);
    $mail->Encoding = 'base64';
    $mail->CharSet = "UTF-8";

    try {

        //Recipients
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
        $mail->addReplyTo('sia.contact@asso-insa-lyon.fr', 'SIA INSA Lyon');
//        $mail->addCustomHeader("List-Unsubscribe",'<' . $unsubscribe_url . '>');
        $mail->addAddress($to, $toName);

        //Content
        $mail->isHTML();
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $noHtmlBody;

        // Send by SMTP
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->Port = getenv('SMTP_PORT');
        $mail->Send();

        return false;
    } catch (Exception $e) {
        error_log("Error sending email: {$mail->ErrorInfo}");
        return "Message can't be sent. Error: {$mail->ErrorInfo}";
    }
}

