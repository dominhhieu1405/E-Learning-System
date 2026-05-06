<?php
namespace Controllers;

use Google\Client as Google_Client;
use Models\User as UserModel;
use Services\Blade;

class Auth
{
    function loginPage(): string
    {
        if (is_login()) {
            response()->redirect(url('home'));
        }
        return (new Blade())->render('user.pages.login', [
            'title' => 'Đăng nhập'
        ]);
    }

    function loginApi(): void
    {
        $username = trim(input()->value('username'));
        $password = input()->value('password');
        $remember = (input()->value('remember') === 'true');

        if (!preg_match('/^[\w@.]{4,64}$/', $username)) {
            response()->json([
                'status' => false,
                'message' => 'Tên tài khoản không hợp lệ (4-64 ký tự)'
            ]);
        }
        if (strlen($password) < 6 || strlen($password) > 64) {
            response()->json([
                'status' => false,
                'message' => 'Mật khẩu phải từ 6-64 ký tự'
            ]);
        }

        response()->json(UserModel::userLogin($username, $password, $remember));
    }

    function registerPage(): string
    {
        if (is_login()) {
            response()->redirect(url('home'));
        }
        return (new Blade())->render('user.pages.register', [
            'title' => 'Đăng ký tài khoản'
        ]);
    }

    function registerApi(): void
    {
        $username = trim(input()->value('username'));
        $password = input()->value('password');
        $confirmPassword = input()->value('confirm_password');
        
        $lastName = trim(input()->value('last_name'));
        $middleFirstName = trim(input()->value('middle_first_name'));
        $email = trim(input()->value('email'));
        $phone = trim(input()->value('phone'));

        if (!preg_match('/^\w{4,32}$/', $username)) {
            response()->json([
                'status' => false,
                'message' => 'Tên tài khoản 4-32 ký tự, chỉ gồm chữ, số, gạch dưới'
            ]);
        }
        if (strlen($password) < 6 || strlen($password) > 64) {
            response()->json([
                'status' => false,
                'message' => 'Mật khẩu phải từ 6-64 ký tự'
            ]);
        }
        if ($password !== $confirmPassword) {
            response()->json([
                'status' => false,
                'message' => 'Mật khẩu xác nhận không khớp'
            ]);
        }

        $fullName = trim($lastName . ' ' . $middleFirstName);
        if (empty($fullName)) {
            $fullName = $username;
        }

        response()->json(UserModel::userRegister($username, $password, [
            'name' => $fullName,
            'last_name' => $lastName,
            'middle_first_name' => $middleFirstName,
            'email' => $email,
            'phone' => $phone
        ]));
    }

    function googleRedirect(): void
    {
        $social = getConf('social');
        $client = new Google_Client();
        $client->setClientId($social['gg_app_id']);
        $client->setClientSecret($social['gg_app_secret']);
        $client->setRedirectUri(url('') . '/auth/google/callback');
        $client->addScope('email');
        $client->addScope('profile');

        $authUrl = $client->createAuthUrl();
        response()->redirect($authUrl);
    }

    function googleCallback(): void
    {
        $code = input()->value('code', null, 'get');
        if (!$code) {
            response()->redirect(url('login'));
            return;
        }

        try {
            $social = getConf('social');
            $client = new Google_Client();
            $client->setClientId($social['gg_app_id']);
            $client->setClientSecret($social['gg_app_secret']);
            $client->setRedirectUri(url('') . '/auth/google/callback');

            $token = $client->fetchAccessTokenWithAuthCode($code);
            if (isset($token['error'])) {
                response()->redirect(url('login'));
                return;
            }

            $client->setAccessToken($token);
            $oauth2 = new \Google\Service\Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            $result = UserModel::findOrCreateGoogleUser(
                $userInfo->getId(),
                $userInfo->getEmail(),
                $userInfo->getName(),
                $userInfo->getPicture()
            );

            if ($result['status']) {
                response()->redirect(url('home'));
            } else {
                response()->redirect(url('login'));
            }
        } catch (\Exception $e) {
            response()->redirect(url('login'));
        }
    }

    function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['login_expire']);
        response()->redirect(url('login'));
    }
}
