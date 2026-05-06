# Danh sách Use Case của Hệ thống OnThi.io.vn (Phía Người dùng)

Dựa trên mã nguồn hiện tại (các Controllers và Router), dưới đây là các Use Case (chức năng) mà người dùng có thể thực hiện trên hệ thống:

## 1. Quản lý Tài khoản & Xác thực (Auth & Profile)
* **Đăng ký tài khoản**: Đăng ký bằng username/password với các thông tin cá nhân.
* **Đăng nhập**: Bằng tài khoản hệ thống hoặc tài khoản Google (OAuth2).
* **Đăng xuất**: Thoát khỏi hệ thống.
* **Xem trang cá nhân**: Xem thông tin cơ bản và lịch sử các bài thi đã làm.
* **Cập nhật thông tin**: Cập nhật Họ tên, số điện thoại, email, ngày sinh, giới tính, tỉnh/thành phố.
* **Đổi ảnh đại diện**: Tải lên và thay đổi avatar cá nhân.
* **Đổi mật khẩu**: Cập nhật mật khẩu mới.

## 2. Khóa học & Bài giảng (Courses)
* **Xem danh sách khóa học**: Xem danh sách tổng hợp, phân trang.
* **Phân loại khóa học**: Xem theo Môn học (Subject) hoặc Lớp học (Class).
* **Xem chi tiết khóa học**: Xem thông tin giới thiệu và lộ trình bài học.
* **Học bài giảng**: Xem chi tiết một bài học (Lesson) và chuyển tiếp Bài trước/Bài sau.

## 3. Tài liệu (Documents)
* **Xem danh sách tài liệu**: Xem tổng hợp tài liệu, phân trang.
* **Phân loại tài liệu**: Lọc tài liệu theo Môn học, Lớp học.
* **Xem chi tiết tài liệu**: Đọc tài liệu cụ thể.

## 4. Thi trắc nghiệm trực tuyến (Exams)
* **Xem danh sách đề thi**: Duyệt toàn bộ đề thi hoặc lọc theo loại kỳ thi (THPT, Đánh giá năng lực HSA, Tư duy TSA...).
* **Xem chi tiết đề thi**: Xem mô tả, cấu trúc đề, số người đã làm và Top 5 bảng xếp hạng.
* **Bắt đầu / Tiếp tục thi**: Tạo phiên thi mới hoặc làm tiếp bài thi đang dang dở.
* **Làm bài thi (Thời gian thực)**:
  * Tải câu hỏi và các nhóm câu hỏi (Passage Group) hoặc câu hỏi Đúng/Sai (TF Items).
  * Hệ thống tự động lưu bài làm (Auto-save) sau một khoảng thời gian.
  * *Đặc thù thi HSA*: Hỗ trợ khóa từng phần thi (Lock Part) và Chọn môn thi tự chọn ở Phần 3 (Branching).
* **Nộp bài thi**: Kết thúc phiên làm bài và chấm điểm.
* **Xem kết quả thi**: Hiển thị điểm số, lịch sử làm bài, đáp án chi tiết và giải thích (nếu cho phép).
* **Bảng xếp hạng (Leaderboard)**: Xem thứ hạng top 100 của một đề thi cụ thể.

## 5. Tương tác Cộng đồng (Comments)
* **Đăng bình luận**: Bình luận trên các trang Đề thi, Khóa học hoặc Tài liệu (hỗ trợ cả bình luận trả lời - reply).
* **Xem bình luận**: Tải danh sách bình luận (có phân trang/tải thêm).

## 6. Tiện ích & Các chức năng khác
* **Tìm kiếm (Search)**: Tìm kiếm Khóa học và Tài liệu bằng từ khóa.
* **Đổi ngôn ngữ**: Chuyển đổi giao diện sang các ngôn ngữ khác (Tiếng Việt, Anh, Hàn, Nhật, Trung, Nga...).
* **Theo dõi trạng thái Online**: Hệ thống tự động đếm lượng người dùng đang truy cập thông qua ping ngầm (Online Tracker).
* **Trang thông tin tĩnh**: Xem Điều khoản sử dụng (Terms) và Câu hỏi thường gặp (FAQs).
