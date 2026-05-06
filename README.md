# OnThi.io.vn - Hệ thống Ôn thi & Đánh giá năng lực trực tuyến

![OnThi.io.vn Banner](https://via.placeholder.com/1200x300.png?text=OnThi.io.vn+-+N%E1%BB%81n+t%E1%BA%A3ng+%C3%94n+thi+Tr%E1%BB%B1c+tuy%E1%BA%BFn)

**Website chính thức:** [https://onthi.io.vn](https://onthi.io.vn)

OnThi.io.vn là một nền tảng giáo dục trực tuyến toàn diện, được thiết kế chuyên biệt để hỗ trợ học sinh trung học phổ thông rèn luyện và chuẩn bị cho các kỳ thi quan trọng như: **THPT Quốc gia**, **Đánh giá năng lực (HSA)**, và **Đánh giá tư duy (TSA)**. Hệ thống mang đến trải nghiệm học tập hiện đại, sát với các định dạng thi thực tế và cung cấp bộ tài liệu, khóa học phong phú.

---

## 🌟 Các tính năng nổi bật

### 1. Hệ thống thi trắc nghiệm mạnh mẽ (Exam Engine)
* **Đa dạng chuẩn thi:** Hỗ trợ cấu trúc đề thi đa dạng bao gồm trắc nghiệm một lựa chọn (Multiple Choice), câu hỏi Đúng/Sai (True/False Clusters), và câu hỏi nhóm ngữ liệu (Passage Groups).
* **Đặc thù bài thi HSA:** Hỗ trợ tính năng tự động khóa từng phần thi (Lock Part) khi hết thời gian và tùy chọn môn thi nhánh (Branching) ở Phần 3 theo sát quy chế thi thực tế của ĐHQGHN.
* **Chống mất dữ liệu:** Hệ thống tự động lưu trữ tiến trình làm bài (Auto-save snapshot) ngầm liên tục, cho phép học sinh tiếp tục bài thi một cách an toàn nếu gặp sự cố mất mạng hoặc vô tình thoát trang.
* **Chấm điểm tự động & Xếp hạng:** Tự động tính điểm, hiển thị đáp án chi tiết, giải thích cụ thể cho từng câu hỏi và cung cấp Bảng xếp hạng (Leaderboard) theo từng đề thi.

### 2. Khóa học & Bài giảng (E-Learning)
* Phân loại khóa học thông minh theo Môn học và Lớp.
* Trình phát video bài giảng tích hợp (hỗ trợ Youtube/Google Drive).
* Lưu trữ và theo dõi tiến độ học tập qua từng bài học (Lesson).

### 3. Kho tài liệu (Documents)
* Lưu trữ các chuyên đề, đề cương, sách ôn tập dưới dạng PDF/DOCX.
* Hỗ trợ tìm kiếm, lọc theo môn học/lớp, đọc thử (preview) trực tiếp trên trình duyệt và tải xuống.

### 4. Hệ thống người dùng (User System)
* Đăng ký và Đăng nhập bảo mật (chuẩn mã hóa mật khẩu BCRYPT).
* Tích hợp đăng nhập nhanh qua tài khoản **Google (OAuth2)**.
* Quản lý hồ sơ cá nhân, ảnh đại diện, đổi mật khẩu.

### 5. Tương tác & Tiện ích mở rộng
* **Hệ thống bình luận:** Thảo luận, đặt câu hỏi tại các đề thi, bài giảng và tài liệu.
* **Đa ngôn ngữ (Multi-language):** Hỗ trợ chuyển đổi ngôn ngữ (Tiếng Việt, Anh, Nhật, Hàn, Trung, Nga).
* Bảo vệ bảo mật nâng cao chống XSS, SQL Injection và hệ thống định tuyến (Router) chặt chẽ.

---

## 🛠 Ngăn xếp Công nghệ (Tech Stack)

Hệ thống được xây dựng trên nền tảng Backend PHP theo mô hình **MVC (Model - View - Controller)** kết hợp với các thư viện mã nguồn mở mạnh mẽ:

* **Backend:** PHP (8.x)
* **Cơ sở dữ liệu:** MySQL (sử dụng thư viện `mysqli-database-class` cho ORM/Query Builder).
* **Routing:** `pecee/simple-router` (Xử lý định tuyến RESTful API).
* **View Engine:** `jenssegers/blade` (Blade Templating engine mang từ Laravel sang).
* **Bảo mật:** `voku/anti-xss` (Ngăn chặn mã độc XSS), Password Hashing an toàn.
* **Tích hợp bên thứ 3:** Google API Client (OAuth2 login).

---

## 🚀 Hướng dẫn Cài đặt Môi trường Phát triển (Local Setup)

### Yêu cầu hệ thống:
- PHP >= 8.0
- MySQL >= 5.7 / MariaDB >= 10.3
- Composer

### Các bước cài đặt:

**1. Clone dự án về máy**
```bash
git clone https://github.com/your-repo/onthi.io.vn.git
cd onthi.io.vn
```

**2. Cài đặt các thư viện (Dependencies)**
```bash
cd backend
composer install
```

**3. Cấu hình hệ thống**
- Điều chỉnh các cấu hình trong `backend/Config/` (Ví dụ: `database.php` kết nối CSDL, `site.php` cài đặt URL...).
- Tạo CSDL MySQL và import database mẫu (nếu có).

**4. Chạy Server ảo (Development)**
Nếu không sử dụng XAMPP/Docker, bạn có thể chạy test trực tiếp bằng PHP Built-in server (trỏ thư mục public làm Document Root):
```bash
php -S localhost:8000 -t public
```
Truy cập `http://localhost:8000` trên trình duyệt.

---

## 📄 Kiến trúc thư mục (Folder Structure)

Dự án được tổ chức tách biệt giữa mã nguồn hệ thống và tài nguyên truy cập công khai:

```
onthi.io.vn/
│
├── backend/               # Thư mục mã nguồn xử lý chính (Không expose ra web)
│   ├── Command/           # Các script CLI
│   ├── Config/            # Các file cấu hình hệ thống (Database, Site, Social...)
│   ├── Controllers/       # Các Controller xử lý logic (Auth, User, Exam, Page...)
│   ├── Helpers/           # Các hàm bổ trợ (function.php, user.php...)
│   ├── Middleware/        # Lớp trung gian kiểm tra xác thực (Auth middleware)
│   ├── Models/            # Lớp tương tác Cơ sở dữ liệu (User, Exam...)
│   ├── Router/            # Nơi khai báo các routes (web.php)
│   ├── vendor/            # Các thư viện Composer tải về
│   └── composer.json      # Danh sách packages
│
├── public/                # Document Root dùng để chạy Web
│   ├── assets/            # CSS, JS, Images (Frontend assets)
│   └── index.php          # Entry point chính của toàn bộ hệ thống
│
├── resources/             # Tài nguyên thô
│   ├── lang/              # Các file dịch thuật đa ngôn ngữ (vi, en, ko, ja...)
│   └── views/             # Các file giao diện Blade templates (.blade.php)
│
└── .gitignore             # Khai báo file bị loại trừ khi push code lên Git
```

---

## 🔒 Bản quyền & Giới hạn
Mã nguồn này được sử dụng cho hệ thống [OnThi.io.vn](https://onthi.io.vn). Hệ thống chỉ bao gồm luồng dành cho Người học (Student flow). Mọi tính năng Admin, Quản trị hệ thống, VIP/Payments và Tích hợp xử lý tự động (AI Parsing) không nằm trong repo mã nguồn ứng dụng này.

---

© 2026 OnThi.io.vn - All rights reserved.
