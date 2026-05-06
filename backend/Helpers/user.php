<?php

use Services\Cache;
use \Models\User as UserModel;

/**
 * @return object Data User
 */
function userget($refresh = true): object
{
    if ($refresh)
        UserModel::refreshUser();

    if (isset($_SESSION['user']) && is_array($_SESSION['user']))
        $_SESSION['user'] = json_decode(json_encode($_SESSION['user']));
    return $_SESSION['user'] ?? object();
}

function user_token($string)
{
    return sha1(md5($string));
}

function is_login()
{
    $user = userget();
    return (isset($user->id));
}

function user_login($token, $user_id, $settings)
{
    $remember_time = time() + (2 * 24 * 60 * 60);
    setcookie("UserID", $user_id, $remember_time, '/');
    setcookie("UserToken", $token, $remember_time, '/');
    if (!empty($settings)) {
        setcookie("mr_settings", $settings, $remember_time, '/');
    }
}

function user_logout()
{
    $remember_time = time() - (365 * 24 * 60 * 60);
    setcookie("UserID", NULL, $remember_time, '/');
    setcookie("UserToken", NULL, $remember_time, '/');

    unset($_SESSION['user_data']);
}
