# Nhật Ký Thay Đổi (Changelog) - Tích Hợp Yahoo Shopping API

Ngày thực hiện: 29/05/2026

## 🚀 Tính Năng Mới (New Features)
- **Tích Hợp Yahoo Shopping**: Hỗ trợ toàn diện việc kết nối và tương tác với các API của Yahoo Shopping dành cho người bán (Seller).
- **Luồng Xác Thực OAuth2**: Triển khai đầy đủ quy trình xác thực OAuth2 cho Yahoo (Bao gồm lấy Authorization Code, trao đổi Access Token và tự động Refresh Token).
- **Giao Tiếp API Bằng XML**: Xây dựng cấu trúc dữ liệu XML tùy chỉnh để phù hợp với định dạng yêu cầu khắt khe (Circus APIs) của Yahoo Shopping.
- **Tính Năng Lấy Đơn Hàng**: Tích hợp các API `OrderList` và `OrderInfo` để có thể truy xuất và đồng bộ dữ liệu đơn hàng từ Yahoo.
- **Cập Nhật Trạng Thái Giao Hàng**: Tích hợp tính năng cập nhật trạng thái đơn hàng (`orderShipStatusChange`) vào `OrderController` để hỗ trợ đẩy thông tin trạng thái lên Yahoo.
- **Quản Lý Cấu Hình Nền Tảng (UI)**: Thêm trường nhập "Seller ID" vào giao diện quản lý kết nối nền tảng, hiển thị linh hoạt (hiện/ẩn) dựa trên lựa chọn nền tảng của người dùng.

## 🛠 Thay Đổi Kỹ Thuật (Technical Changes)
- **Cơ Sở Dữ Liệu**: Bổ sung migration để thêm cột `seller_id` (kiểu chuỗi, có thể null) vào bảng `platform_connections`, dùng để lưu trữ Seller ID bắt buộc từ Yahoo. Cập nhật Model tương ứng (`$fillable`).
- **Lớp Mới (New Class)**: Tạo lớp `App\Connectors\YahooConnector` triển khai Interface `OAuthConnector`, chứa toàn bộ logic tương tác với API của Yahoo.
- **Tuyến Đường (Routes)**: Đăng ký các Web Routes dành riêng cho việc chuyển hướng (redirect) và nhận phản hồi (callback) của luồng xác thực Yahoo OAuth2.
- **Xử Lý Lỗi Dữ Liệu (Error Handling)**: Tích hợp logic bóc tách thông báo lỗi (parse XML) từ API của Yahoo để trả về những thông báo dễ đọc cho hệ thống.

## 🐛 Sửa Lỗi (Bug Fixes)
- Cấu hình lại file `resources/views/btoc/shop/detail.blade.php`: Sửa lỗi cú pháp `ParseError` do dư thừa thẻ `@endif` trong quá trình thêm tính năng ẩn/hiện Seller ID.

## ✅ Kiểm Thử (Testing)
- Xác nhận các chức năng và thành phần hệ thống hiện tại đều hoạt động ổn định không bị ảnh hưởng (Regression Tests passed) thông qua `php artisan test`.
