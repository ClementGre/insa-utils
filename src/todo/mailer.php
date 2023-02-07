<?php
function send_auth_mail($name, $email_prefix, $id, $email_token, $email_code) : void {

    $message = "Bien le bonjour,
Veuillez suivre ce lien pour vous authentifier sur insa-utils :

https://insa-utils.live/todo/auth?id=" . $id . '&email_token=' . $email_token . '&email_code=' . $email_code . "

Autrement, entrez le code suivant sur la page : " . $email_code . "

Cordialement,
L'équipe d'insa-utils";

    $subjet = 'Authentification insa-utils';

    $unsubscribe_url = "https://insa-utils.live/todo/unsubscribe?&id=" . $id . '&email_token=' . $email_token . '&email_code=' . $email_code;

    send_mail($name, $email_prefix, $subjet, $message, $unsubscribe_url);
}

function send_mail($name, $email, $subject, $content, $unsubscribe_url): void {

    $headers = "From: insa-utils <auth@insa-utils.live>\n"
            . "List-Unsubscribe:  <" . $unsubscribe_url . ">\n"
            . "Reply-To: <clement.grennerat@insa-lyon.fr>";

    mail($name . ' <' . $email . '>', $subject, $content, $headers);
}