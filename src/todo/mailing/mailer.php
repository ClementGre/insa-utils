<?php
function send_auth_mail($name, $email_prefix, $id, $email_token, $email_code) : void {

    require_once __DIR__.'/../mailing/auth_content.php';

    $url = urlencode("https://insa-utils.live/todo/?id=" . $id . '&token=' . $email_token);
    $unsubscribe_url = urlencode("https://insa-utils.live/todo/unsubscribe?&id=" . $id . '&token=' . $email_token);

    $text = get_auth_mail_text_content($url, $email_code, $unsubscribe_url);
    $html = get_auth_mail_content($url, $email_code, $unsubscribe_url);

    $subject = 'Authentification sur insa-utils';

    send_mail($name, $email_prefix . '@insa-lyon.fr', $subject, $text, $html, $unsubscribe_url);
}

function send_mail($name, $email, $subject, $text, $html, $unsubscribe_url): void {

    if (strpos($_SERVER['HTTP_HOST'], 'localhost') === 0) {
        return;
    }

    $headers = "From: insa-utils <auth@insa-utils.live>
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

