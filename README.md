# CopBida - Hệ thống quản lý tiệm bida

Ứng dụng quản lý tiệm bida theo phong cách KiotViet, được xây dựng trên Laravel + Bootstrap 5.

## Tính năng chính

- Phân quyền Admin / Nhân viên
- Quản lý bàn, sơ đồ bàn, mở/đóng bàn tính tiền theo giờ chơi
- POS bán hàng với giỏ hàng lưu localStorage (không mất khi F5)
- Quản lý sản phẩm, danh mục, tồn kho
- Quản lý nhân viên
- Báo cáo doanh thu theo ngày / tuần / tháng / năm / khoảng thời gian tùy chọn
- Giao diện responsive, phù hợp cả PC và mobile

## Yêu cầu

- PHP >= 8.2
- Composer
- MySQL / MariaDB (hoặc SQLite cho môi trường phát triển)

## Cài đặt

```bash
composer install
cp .env.example .env
# Cấu hình DB trong .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

## Tài khoản mặc định

- Admin: `admin` / `admin123`
- Staff: `staff` / `staff123`
