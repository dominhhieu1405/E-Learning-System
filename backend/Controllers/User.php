<?php
namespace Controllers;

# use Exception;
use Models\Model;
use Services\Blade;
# use voku\helper\AntiXSS;
use Models\User as UserModel;

class User
{
    function index() : string
    {
        $exams = \Models\Exam::listExams(null, 1, 1, 6);
        $courses = \Models\Web::courses(1, 4);
        $documents = \Models\Web::documents(1, 4);

        return (new Blade())->render('user.pages.home', [
            'title' => 'Home',
            'navbar' => 'home',
            'exams' => $exams,
            'courses' => $courses,
            'documents' => $documents,
        ]);
    }

    function authLogin() : bool|string
    {

        if (is_login()) {
            response()->redirect(url('home'));
        }
        if(!request()->isAjax()){
            return (new Blade())->render('user.pages.login');
        }
        $username = trim(input()->value('username'));
        $password = input()->value('password');
        $remember = (input()->value('remember') === 'true');
        if (!preg_match('/^[\w@.]{4,64}$/', $username))
            response()->json([
                'status' => false,
                'message' => 'Tên tài khoản không hợp lệ'
            ]);
        else if (6 > strlen($password) || 64 < strlen($password))
            response()->json([
                'status' => false,
                'message' => 'Mật khẩu phải từ 6-64 ký tự'
            ]);
        else
            response()->json(UserModel::userLogin($username, $password, $remember));
        return true;
    }


    function logout() : void
    {
        unset($_SESSION['user']);
        response()->redirect(url('login'));
    }



    function profile() : string
    {
        if (!is_login()) response()->redirect(url('login'));
        
        $user = UserModel::userById(userget()->id);
        $sessions = \Models\ExamSession::getUserSessions(userget()->id, 50);

        return (new Blade())->render('user.pages.profile', [
            'title' => 'Trang cá nhân',
            'navbar' => 'profile',
            'user' => (object)$user,
            'sessions' => $sessions,
            'provinces' => $this->getProvincesList()
        ]);
    }

    private function getProvincesList() {
        return [
            1 => "Hà Nội", 2 => "TP. Hồ Chí Minh", 3 => "Hải Phòng", 4 => "Đà Nẵng", 5 => "Cần Thơ",
            6 => "An Giang", 7 => "Bà Rịa - Vũng Tàu", 8 => "Bắc Giang", 9 => "Bắc Kạn", 10 => "Bạc Liêu",
            11 => "Bắc Ninh", 12 => "Bến Tre", 13 => "Bình Định", 14 => "Bình Dương", 15 => "Bình Phước",
            16 => "Bình Thuận", 17 => "Cà Mau", 18 => "Cao Bằng", 19 => "Đắk Lắk", 20 => "Đắk Nông",
            21 => "Điện Biên", 22 => "Đồng Nai", 23 => "Đồng Tháp", 24 => "Gia Lai", 25 => "Hà Giang",
            26 => "Hà Nam", 27 => "Hà Tĩnh", 28 => "Hải Dương", 29 => "Hậu Giang", 30 => "Hòa Bình",
            31 => "Hưng Yên", 32 => "Khánh Hòa", 33 => "Kiên Giang", 34 => "Kon Tum"
            // Reached 34 as requested (subset of 63 for demo or specific demand)
        ];
    }

    function updateProfileApi(): void
    {
        if (!is_login() || !request()->isAjax()) return;

        $lastName = trim(input()->value('last_name'));
        $middleFirstName = trim(input()->value('middle_first_name'));
        
        $email = trim(input()->value('email'));
        $data = [
            'last_name' => $lastName,
            'middle_first_name' => $middleFirstName,
            'display_name' => trim($lastName . ' ' . $middleFirstName),
            'phone' => trim(input()->value('phone')),
            'email' => $email,
            'birthday' => input()->value('birthday'),
            'gender' => (int)input()->value('gender'),
            'province_id' => (int)input()->value('province_id')
        ];

        if (empty($data['middle_first_name'])) {
            response()->json(['status' => false, 'message' => 'Tên không được để trống']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response()->json(['status' => false, 'message' => 'Định dạng email không hợp lệ']);
            return;
        }

        UserModel::updateUser(userget()->id, $data);
        UserModel::refreshUser();

        response()->json(['status' => true, 'message' => 'Cập nhật thông tin thành công!']);
    }

    function uploadAvatarApi(): void
    {
        if (!is_login()) return;
        
        $file = $_FILES['avatar_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            response()->json(['status' => false, 'message' => 'Không tìm thấy file hoặc lỗi upload']);
            return;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($ext), $allowed)) {
            response()->json(['status' => false, 'message' => 'Định dạng file không hỗ trợ']);
            return;
        }

        $newName = 'avatar_' . userget()->id . '_' . time() . '.' . $ext;
        $uploadDir = ROOT_PATH . '/public/uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            $avatarUrl = '/uploads/avatars/' . $newName;
            UserModel::updateUser(userget()->id, ['avatar' => $avatarUrl]);
            UserModel::refreshUser();
            response()->json(['status' => true, 'url' => $avatarUrl]);
        } else {
            response()->json(['status' => false, 'message' => 'Không thể lưu file']);
        }
    }

    function changePasswordApi(): void
    {
        if (!is_login() || !request()->isAjax()) return;

        $old = input()->value('old_password');
        $new = input()->value('new_password');
        $confirm = input()->value('confirm_password');

        $user = UserModel::userById(userget()->id);

        if (!password_verify($old, $user['password'])) {
            response()->json(['status' => false, 'message' => 'Mật khẩu cũ không chính xác']);
            return;
        }

        if (strlen($new) < 6 || strlen($new) > 64) {
            response()->json(['status' => false, 'message' => 'Mật khẩu mới phải từ 6-64 ký tự']);
            return;
        }

        if ($new !== $confirm) {
            response()->json(['status' => false, 'message' => 'Xác nhận mật khẩu không khớp']);
            return;
        }

        UserModel::updateUser(userget()->id, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        response()->json(['status' => true, 'message' => 'Đổi mật khẩu thành công!']);
    }


}