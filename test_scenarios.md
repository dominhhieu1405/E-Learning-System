# Kịch bản Kiểm thử (Test Scenarios) - OnThi.io.vn

Dựa trên danh sách các Use Case, dưới đây là các kịch bản kiểm thử (Test Scenarios) cơ bản nhằm đảm bảo hệ thống hoạt động ổn định và chính xác.

## 1. Quản lý Tài khoản & Xác thực (Auth & Profile)

### UC1.1: Đăng ký tài khoản
- **TC_01**: Đăng ký thành công với thông tin hợp lệ (tên tài khoản chưa tồn tại, mật khẩu đủ điều kiện).
- **TC_02**: Đăng ký thất bại khi để trống các trường bắt buộc.
- **TC_03**: Đăng ký thất bại khi nhập username/email đã tồn tại trên hệ thống.
- **TC_04**: Đăng ký thất bại khi mật khẩu và xác nhận mật khẩu không khớp hoặc độ dài không đủ.

### UC1.2: Đăng nhập & Đăng xuất
- **TC_05**: Đăng nhập thành công với tài khoản và mật khẩu đúng.
- **TC_06**: Đăng nhập thất bại với tài khoản hoặc mật khẩu không chính xác (kiểm tra thông báo lỗi).
- **TC_07**: Đăng nhập/Đăng ký thành công thông qua cổng Google (OAuth2).
- **TC_08**: Đăng xuất thành công, phiên đăng nhập (session) bị hủy và điều hướng về trang chủ/đăng nhập.
- **TC_09**: Không thể truy cập trực tiếp các URL yêu cầu đăng nhập (như `/user`, `/exam/.../start`) sau khi đã đăng xuất (phải bị redirect).

### UC1.3: Xem và cập nhật trang cá nhân
- **TC_10**: Truy cập trang cá nhân hiển thị chính xác thông tin User và danh sách lịch sử bài thi.
- **TC_11**: Cập nhật thông tin thành công với dữ liệu hợp lệ (Họ tên, email, ngày sinh, SĐT, giới tính).
- **TC_12**: Thay đổi ảnh đại diện (avatar) thành công với file hình ảnh đúng định dạng và kích thước.
- **TC_13**: Tải ảnh đại diện thất bại khi gửi file không phải là hình ảnh (.pdf, .exe...) hoặc vượt quá dung lượng.
- **TC_14**: Đổi mật khẩu thành công khi nhập đúng mật khẩu cũ và mật khẩu mới hợp lệ.
- **TC_15**: Đổi mật khẩu thất bại khi nhập sai mật khẩu cũ.

---

## 2. Khóa học & Bài giảng (Courses)

### UC2.1: Xem danh sách và lọc khóa học
- **TC_16**: Truy cập trang danh sách khóa học, dữ liệu hiển thị đầy đủ và tính năng phân trang (Next, Prev) hoạt động đúng.
- **TC_17**: Lọc khóa học theo Môn học (Subject) trả về kết quả chính xác theo môn.
- **TC_18**: Lọc khóa học theo Lớp học (Class) trả về kết quả chính xác theo lớp.

### UC2.2: Xem chi tiết và học bài giảng
- **TC_19**: Hiển thị đúng thông tin chi tiết và lộ trình (danh sách bài học) của một khóa học.
- **TC_20**: Nhấp vào một bài học (Lesson) mở ra giao diện học thành công và hiển thị nội dung/video bài học.
- **TC_21**: Chuyển tiếp "Bài sau" (Next) và lùi lại "Bài trước" (Prev) hoạt động chính xác.

---

## 3. Tài liệu (Documents)

### UC3.1: Duyệt và xem chi tiết tài liệu
- **TC_22**: Danh sách tài liệu load thành công, tính năng phân trang hoạt động tốt.
- **TC_23**: Bộ lọc tài liệu (theo Môn học, Lớp học) trả về đúng danh sách tài liệu tương ứng.
- **TC_24**: Mở một tài liệu cụ thể hiển thị đúng định dạng nội dung (PDF, text, ảnh).

---

## 4. Thi trắc nghiệm trực tuyến (Exams)

### UC4.1: Danh sách và Chi tiết đề thi
- **TC_25**: Trang danh sách đề thi phân loại chính xác các tab (THPT, HSA, TSA).
- **TC_26**: Xem chi tiết đề thi hiển thị khớp cấu trúc đề (số câu hỏi, thời gian) và Top 5 xếp hạng.

### UC4.2: Bắt đầu & Làm bài thi
- **TC_27**: Bắt đầu phiên thi mới thành công (chuyển sang trạng thái làm bài) đối với người dùng đã đăng nhập.
- **TC_28**: Khôi phục (Resume) bài thi đang dang dở thành công: Thời gian đếm ngược chính xác với thời gian còn lại, các đáp án đã chọn trước đó được khôi phục.
- **TC_29**: Tải đầy đủ và hiển thị đúng các dạng câu hỏi: Câu hỏi đơn, Nhóm câu hỏi đọc hiểu (Passage), và Câu hỏi Đúng/Sai (TF).
- **TC_30**: Auto-save: Hệ thống tự động lưu đáp án sau một khoảng thời gian (hoặc khi tích chọn), load lại trang không bị mất đáp án.
- **TC_31**: Đồng hồ đếm ngược hoạt động đúng. Khi hết giờ, hệ thống cảnh báo và tự động trigger hàm nộp bài.

### UC4.3: Tính năng đặc thù thi Đánh giá năng lực (HSA)
- **TC_32**: Tính năng Khóa phần thi (Lock part): Sau khi xác nhận khóa một phần, hệ thống không cho phép quay lại sửa đáp án phần đó và chuyển sang phần tiếp theo.
- **TC_33**: Tính năng Chọn nhánh (Branching): Tới phần 3 của đề HSA, bảng chọn môn thi xuất hiện. Khi chọn môn, hệ thống tải đúng bộ câu hỏi của nhánh đó.

### UC4.4: Nộp bài và Xem kết quả
- **TC_34**: Nộp bài chủ động (trước khi hết giờ) thành công. Hệ thống chấm điểm chính xác (so sánh bài làm và đáp án gốc).
- **TC_35**: Mở trang Xem kết quả: Hiển thị đúng số điểm, câu đúng/sai/bỏ qua, đáp án chi tiết và lời giải thích cho từng câu.
- **TC_36**: (Case Ẩn đáp án) Nếu đề thi cấu hình không cho xem đáp án, người dùng chỉ thấy điểm tổng và số câu đúng, không xem được chi tiết từng câu.
- **TC_37**: Mở Bảng xếp hạng (Leaderboard) của đề thi, hiển thị đúng thứ hạng của User trong Top 100.

---

## 5. Tương tác Cộng đồng (Comments)

### UC5.1: Quản lý Bình luận
- **TC_38**: User đã đăng nhập gửi bình luận mới thành công trên trang đề thi/khóa học/tài liệu. Nội dung hiển thị ngay lập tức.
- **TC_39**: Gửi trả lời (reply) cho một bình luận đã có thành công, bình luận con nằm thụt lề dưới bình luận cha.
- **TC_40**: Tính năng Tải thêm bình luận (Load more / Phân trang) hoạt động khi số lượng bình luận vượt quá giới hạn hiển thị.
- **TC_41**: Guest (chưa đăng nhập) bị chặn và yêu cầu đăng nhập khi cố gắng gửi bình luận.

---

## 6. Tiện ích & Các chức năng khác

### UC6.1: Tiện ích chung
- **TC_42**: Thanh Tìm kiếm (Search) trả về kết quả tìm kiếm gồm cả Khóa học và Tài liệu có tên khớp/gần khớp với từ khóa.
- **TC_43**: Trình đổi ngôn ngữ: Chuyển đổi ngôn ngữ thành công, giao diện tự reload và tất cả văn bản tĩnh dịch sang ngôn ngữ đích (Vi, En, Zh, Ko...).
- **TC_44**: Bộ đếm Online (Online tracker): Hệ thống đếm chính xác tổng số User có tương tác trong khoảng thời gian timeout quy định.
- **TC_45**: Kiểm tra các trang nội dung tĩnh (Điều khoản, FAQ) hiển thị đầy đủ, không bị lỗi font hay bố cục ở tất cả ngôn ngữ.
