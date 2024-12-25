<?php

require __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function send_auth_mail($name, $email_prefix, $id, $email_token, $email_code, $redirect): void
{

    require_once __DIR__ . '/../mailing/auth_content.php';

    $url = "https://insa-utils.fr/account/login?id=" . $id . '&token=' . $email_token . '&redirect=' . $redirect;
    $unsubscribe_url = "https://insa-utils.fr/account/unsubscribe?&id=" . $id . '&token=' . $email_token;

    $text = get_auth_mail_text_content($url, $email_code, $unsubscribe_url);
    $html = get_auth_mail_content($url, $email_code, $unsubscribe_url);

    $subject = 'Authentification sur insa-utils';

    if (str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
        return;
    }

    sendMail($name, $email_prefix . '@insa-lyon.fr', $subject, $html, $text);
}
function send_disable_email_mail($name, $email_prefix, $id, $email_resubscribe_token): void
{

    require_once __DIR__ . '/../mailing/disable_email_content.php';

    $url = "https://insa-utils.fr/account/unsubscribe?id=" . $id . '&resubscribe_token=' . $email_resubscribe_token;

    $text = get_disable_email_mail_text_content($url);
    $html = get_disable_email_mail_content($url);

    $subject = 'Désactivation de la réception d\'emails d\'insa-utils';

    if (str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
        return;
    }
    sendMail($name, $email_prefix . '@insa-lyon.fr', $subject, $html, $text);
}

// Deprecated
function send_mail($name, $email, $subject, $text, $html, $unsubscribe_url): void
{

    $headers = "From: insa-utils <auth@insa-utils.fr>
List-Unsubscribe:  <" . $unsubscribe_url . ">
Reply-To: <clement.grennerat@insa-lyon.fr>
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary=\"----=_NextPart_DC7E1BB5_1105_4DB3_BAE3_2A6208EB099D\"";

    $message = "------=_NextPart_DC7E1BB5_1105_4DB3_BAE3_2A6208EB099D
Content-type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

" . $text . "

------=_NextPart_DC7E1BB5_1105_4DB3_BAE3_2A6208EB099D
Content-type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable

" . $html . "

------=_NextPart_DC7E1BB5_1105_4DB3_BAE3_2A6208EB099D--";

    mail($name . ' <' . $email . '>', $subject, $message, $headers);
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
    $mail = new PHPMailer(true);
    $mail->Encoding = 'base64';
    $mail->CharSet = "UTF-8";

    try {

        //Recipients
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
        $mail->addReplyTo('clement.grennerat@insa-lyon.fr', 'Clément Grennerat');
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
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->Port = getenv('SMTP_PORT');
        $mail->Send();

        return false;
    } catch (Exception $e) {
        return "Message can't be sent. Error: {$mail->ErrorInfo}";
    }
}

