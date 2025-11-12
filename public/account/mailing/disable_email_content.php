<?php

function get_disable_email_mail_text_content($url) : string
{
    return  "Vous avez demandé de ne plus recevoir d'emails d'INSA Utils.
Vous ne recevrez plus d'emails de INSA Utils, ce qui vous empêchera de vous authentifier sur d'autres appareils.

Veuillez suivre ce lien pour réactiver les emails et ainsi pouvoir vous authentifier sur d'autres appareils :
" . $url . "

Conservez cet email, autrement, vous perdrez accès à votre compte.

Si vous n'êtes pas à l'origine de cette demande, veuillez suivre le lien donné ci-dessus pour réactiver la réception d'emails d'INSA Utils.";
}

function get_disable_email_mail_content($url) : string
{

    $texturl = implode("&#8203;", str_split($url, 10));

    return <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale: 1.0;">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table bgcolor="#EDF2F4" width="100%" border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td align="center" bgcolor="#D90429">
            <table width="90%" align="center" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="20" style="font-size: 20px; line-height: 20px">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 30px; color: white; font-weight: 600; font-family: Verdana, Arial, Helvetica sans-serif">
                        Ne plus recevoir d'emails d'INSA Utils
                    </td>
                </tr>
                <tr>
                    <td height="20" style="font-size: 20px; line-height: 20px">&nbsp;</td>
                </tr>
                </tbody>
            </table>

        </td>
    </tr>
    <tr>
        <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
    </tr>
    <tr>
        <td align="center">
            <table width="90%">
                <tbody>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 15px; color: #2B2D42; font-weight: 400; font-family: Verdana, Arial, Helvetica sans-serif">
                        Vous avez demandé de ne plus recevoir d'emails d'INSA Utils.
                    </td>
                </tr>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 15px; color: #2B2D42; font-weight: 400; font-family: Verdana, Arial, Helvetica sans-serif">
                        Vous ne recevrez plus d'emails de INSA Utils, ce qui vous empêchera de vous authentifier sur d'autres appareils.
                    </td>
                </tr>
                <tr>
                    <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 15px; color: #2B2D42; font-weight: 400; font-family: Verdana, Arial, Helvetica sans-serif">
                        Veuillez cliquer sur le bouton suivant pour réactiver les emails et ainsi pouvoir vous authentifier sur d'autres appareils.
                    </td>
                </tr>
                <tr>
                    <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center" style="text-align: center;">
                        <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                     xmlns:w="urn:schemas-microsoft-com:office:word"
                                     href="$url"
                                     style="height:53px;v-text-anchor:middle; arcsize=" 19%"
                        strokecolor="#000000"
                        fillcolor="#EF233C">
                        <w:anchorlock/>
                        <center style="color:#ffffff;font-family: Verdana, Arial, Helvetica sans-serif;font-size:15px;font-weight:bold;width:300px;">
                            Confirmer l'authentification
                        </center>
                        </v:roundrect>
                        <![endif]-->
                        <a href="$url"
                           style="background-color:#2B2D42;border-radius:10px;color:#ffffff;display:inline-block;font-family: Verdana, Arial, Helvetica sans-serif;font-size:15px;font-weight:bold;line-height:40px;width:300px;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;mso-hide:all;">
                            Réactiver les emails
                        </a>
                    </td>
                </tr>
                <tr>
                    <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 11px; color: #2B2D42; font-weight: 400; font-family: Verdana, Arial, Helvetica sans-serif;">
                        Si le bouton n'apparaît pas correctement, veuillez suivre ce lien :
                        <br>
                        <a href="$url">$texturl</a>
                    </td>
                </tr>
                <tr>
                    <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"
                        style="text-align: center; font-size: 11px; color: #2B2D42; font-weight: 400; font-family: Verdana, Arial, Helvetica sans-serif">
                        Si vous n'êtes pas à l'origine de cette demande, veuillez suivre le lien donné ci-dessus pour réactiver la réception d'emails d'INSA Utils.
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td height="30" style="font-size: 30px; line-height: 30px">&nbsp;</td>
    </tr>
    </tbody>
</table>
</body>
</html>
EOT;
}
