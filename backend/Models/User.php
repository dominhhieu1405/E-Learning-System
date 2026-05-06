<?php

namespace Models;

use Exception;

class User extends Model
{

    static function userRegister($username, $password, $extra = []): array
    {
        // Check username tồn tại
        $existing = User::getDB()->where('username', $username)->objectBuilder()->getOne('users');
        if ($existing) {
            return ['status' => false, 'message' => 'Tên tài khoản đã tồn tại!'];
        }

        if (is_string($extra)) {
            $name = $extra;
            $extra = ['name' => $name, 'display_name' => $name];
        }

        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $extra['name'] ?? $username,
            'display_name' => $extra['name'] ?? $username,
            'last_name' => $extra['last_name'] ?? null,
            'middle_first_name' => $extra['middle_first_name'] ?? null,
            'email' => $extra['email'] ?? null,
            'phone' => $extra['phone'] ?? null,
            'role' => 0,
            'status' => 1,
        ];

        $id = User::getDB()->insert('users', $data);
        if ($id) {
            $user = User::getDB()->where('id', $id)->objectBuilder()->getOne('users');
            $_SESSION['user'] = (object) $user;
            $_SESSION['login_expire'] = time() + 3600 * 24 * 30;
            return ['status' => true, 'message' => 'Đăng ký thành công!'];
        }
        return ['status' => false, 'message' => 'Đăng ký thất bại!'];
    }

    static function findOrCreateGoogleUser($google_id, $email, $name, $avatar): array
    {
        // Tìm theo google_id
        $user = User::getDB()->where('google_id', $google_id)->objectBuilder()->getOne('users');
        if ($user) {
            $_SESSION['user'] = (object) $user;
            $_SESSION['login_expire'] = time() + 3600 * 24 * 30;
            return ['status' => true, 'message' => 'Đăng nhập Google thành công!'];
        }

        // Tìm theo email (username = email)
        $user = User::getDB()->where('username', $email)->objectBuilder()->getOne('users');
        if ($user) {
            // Link google_id
            User::getDB()->where('id', $user->id)->update('users', [
                'google_id' => $google_id,
                'avatar' => $avatar,
                'display_name' => $name,
            ]);
            $user = User::getDB()->where('id', $user->id)->objectBuilder()->getOne('users');
            $_SESSION['user'] = (object) $user;
            $_SESSION['login_expire'] = time() + 3600 * 24 * 30;
            return ['status' => true, 'message' => 'Đăng nhập Google thành công!'];
        }

        // Tạo mới
        $data = [
            'username' => $email,
            'password' => password_hash(RandStr(16), PASSWORD_DEFAULT),
            'name' => $name,
            'display_name' => $name,
            'google_id' => $google_id,
            'avatar' => $avatar,
            'role' => 0,
            'status' => 1,
        ];

        $id = User::getDB()->insert('users', $data);
        if ($id) {
            $user = User::getDB()->where('id', $id)->objectBuilder()->getOne('users');
            $_SESSION['user'] = (object) $user;
            $_SESSION['login_expire'] = time() + 3600 * 24 * 30;
            return ['status' => true, 'message' => 'Đăng ký Google thành công!'];
        }

        return ['status' => false, 'message' => 'Đăng nhập Google thất bại!'];
    }


    static function create(array $data): int|null
    {
        return User::getDB()->insert('users', $data);
    }

    static function userById(int $id): array|null
    {
        return User::getDB()->where('id', $id)->getOne('users');
    }

    static function updateUser(int $id, array $data): bool
    {
        return User::getDB()->where('id', $id)->update('users', $data);
    }

    static function getAllUsers()
    {
        return User::getDB()->objectBuilder()->orderBy('time_join', 'DESC')->get('users');
    }

    static function userFromData($col, $val)
    {
        try {
            return User::getDB()->where($col, $val)->objectBuilder()
                ->getOne('users');
        } catch (Exception $e) {
            return null;
        }
    }
    static function userLogin($username, $password, $remember = true): array
    {
        $check = (object) User::getDB()->where('username', $username)
            ->objectBuilder()
            ->getOne('users');
        if (empty($check->id) || !password_verify($password, $check->password)) {
            return [
                'status' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác'
            ];
        }
        if ($check->status === -1) {
            return [
                'status' => false,
                'message' => 'Tài khoản chưa được xác nhận!'
            ];
        }
        if ($check->status === 2) {
            return [
                'status' => false,
                'message' => 'Tài khoản đã bị khóa!'
            ];
        }
        $_SESSION['user'] = (object) $check;
        if (!$remember) {
            // 12h
            $_SESSION['login_expire'] = time() + 3600 * 12;
        } else {
            // 30 ngày
            $_SESSION['login_expire'] = time() + 3600 * 24 * 30;
        }
        return [
            'status' => true,
            'message' => 'Đăng nhập thành công!',
        ];
    }

    static function userGet(string $column, int|null $id = null): mixed
    {
        $id = $id ?? userget()->id;
        $row = User::getDB()->where('id', $id)->getOne('users');
        return @$row[$column];
    }

    static function refreshUser(): void
    {
        $id = @userget(null)->id;
        $row = User::getDB()->where('id', $id)->objectBuilder()
            ->getOne('users');
        $_SESSION['user'] = (object) $row;
    }

}