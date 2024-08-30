# update-view

## Overview

`WP_view_update_API` là một class dùng để cập nhật lượt xem bài viết/ Custom Conent Type (PODS Plguin) trong WordPress. Nó tạo ra một API endpoint để cập nhật số lượng view của bài viết dựa trên permalink và cung cấp chức năng đặt lại các lượt xem hàng ngày, hàng tuần, và hàng tháng thông qua cron jobs.

## Class: `WP_view_update_API`

## Installation

1. **Tải xuống và cài đặt hoặc chèn code trong file function**
2. **Chỉnh sử thông tin bảng, cột, endpoint phù hợp
    ```php
new WP_view_update_API('update-view' ,'wp_pods_movie', 'post_views', 'post_view_day', 'post_view_week', 'post_view_month');
 ```
- **Thay thế function lấy permalink:**
    Nếu không sử dụng Pods để lấy permalink thông quan pods_v, bạn có thể thay thế bằng hàm khác như sau:

    ```php
    // Thay thế lấy permalink nếu không sử dụng Pods
    $permalink = get_permalink($post_id); // Thay $post_id với ID bài viết
    ```

## Chạy Code

Để đảm bảo code được chạy ở chân trang, hãy thêm đoạn mã trong file script trong file single
