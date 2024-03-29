<?php
function randomToken($length)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($chars);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}

function randomCode($length)
{
    $chars = '0123456789';
    $input_length = strlen($chars);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}

// $email does not include @insa-lyon.fr
function emailToName($email)
{
    return ucwords(strtolower(str_replace('.', ' ', $email)));
}

// $email does not include @insa-lyon.fr
function nameToEmail($name)
{
    return strtolower(str_replace(' ', '.', $name));
}

function timestampDiffMn($timestamp)
{
    if ($timestamp == null) {
        return strtotime("now");
    }
    return (strtotime("now") - strtotime($timestamp)) / 60;
}

function print_session_messages(): void
{
    if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0) {
        print_messages($_SESSION['errors'], true);
        $_SESSION['errors'] = array();
    }
    if (isset($_SESSION['infos']) && count($_SESSION['infos']) > 0) {
        print_messages($_SESSION['infos'], false);
        $_SESSION['infos'] = array();
    }
}

function print_messages($array, $is_error): void
{
    if (isset($array) && count($array) > 0) {
        ?>
        <div class="infos-container">
            <div class="<?= $is_error ? 'errors' : 'success' ?>">
                <?php
                foreach ($array as $message) {
                    echo '<p>' . $message . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}

function set_cookie($name, $value): void
{
    setcookie($name, $value, [
        'expires' => time() + 60 * 60 * 24 * 365 * 5, // 5 years
        'path' => getRootPath(),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function remove_cookie($name): void
{
    setcookie($name, '', [
        'expires' => time(),
        'path' => getRootPath(),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}


function endsWith($haystack, $needle): bool
{
    $length = strlen($needle);
    if (!$length) return true;
    return substr($haystack, -$length) === $needle;
}
function date_today(): string
{
    return (new DateTime())->format('Y-m-d');
}
function date_tomorrow(): string
{
    return (new DateTime())->add(new DateInterval('P1D'))->format('Y-m-d');
}
function end_of_class_year(): string
{
    if((new DateTime())->format('m') < 7){
        return (new DateTime())->format('Y') . '-06-30';
    }else{
        return (intval((new DateTime())->format('Y')) + 1) . '-06-30';
    }
}
