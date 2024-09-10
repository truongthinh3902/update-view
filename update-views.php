<?php


class WP_view_update_API {
	private $table_name;
	private $post_views;
	private $post_view_day;
	private $post_view_week;
	private $post_view_month;

	// Constructor nhận các tham số đầu vào để thiết lập các thuộc tính
	public function __construct(
        $table_name = 'wp_pods_comics', 
        $post_views = 'post_view', 
        $post_view_day = 'views_day', 
        $post_view_week = 'views_week', 
        $post_view_month = 'views_month'
        ) {
		 // Gán giá trị đầu vào cho các thuộc tính của class
		 $this->table_name = $table_name;
		 $this->post_views = $post_views;
		 $this->post_view_day = $post_view_day;
		 $this->post_view_week = $post_view_week;
		 $this->post_view_month = $post_view_month;

		// Đăng ký API endpoint khi khởi tạo class
		add_action('rest_api_init', [$this, 'register_api_endpoints']);
		add_action('resert_view_daily', [$this, 'cron_job_reset_view_daily']);
		add_action('resert_view_week', [$this, 'cron_job_reset_view_week']);
		add_action('resert_view_month', [$this, 'cron_job_reset_view_month']);
        // add_action('wp_enqueue_scripts', 'my_enqueue_scripts_view');
		$this->cron_job_resert_view();
	}

	// Đăng ký các endpoint cho WP REST API
	public function register_api_endpoints() {
		 register_rest_route('post-api/v1', '/update-view/', [
			  'methods' => 'POST',
			  'callback' => [$this, 'update_post_view'],
			  'permission_callback' => '__return_true', // Có thể thay đổi theo nhu cầu
		 ]);
	} 


	// Hàm để cập nhật số lượng view của bài viết
	public function update_post_view($request) {
		 global $wpdb;

		 // Lấy permalink từ request
		 $permalink = sanitize_text_field($request['permalink']); // Đảm bảo rằng permalink được xử lý an toàn

		 // Lấy số lượng view hiện tại từ database
		 $sql = $wpdb->prepare(
			  "SELECT {$this->post_views}, {$this->post_view_day}, {$this->post_view_week}, {$this->post_view_month} FROM {$this->table_name} WHERE slug = %s",
			  $permalink
		 );
		 $results = $wpdb->get_results($sql);
		 if(empty($results)) { 
			// Nếu không tìm thấy permalink, trả về thông báo lỗi
			return new WP_REST_Response(['message' => 'Không tìm thấy bài viết'], 404);
			die;
		 }
		 $request = $results[0];
		 // update view 
		 if(is_numeric($request->{$this->post_views})) {
			$value_post_views = $request->{$this->post_views} + 1;
			
		 } else {
			$value_post_views = 1;
		 }
		 if(is_numeric($request->{$this->post_view_day})) {
			$value_post_view_day = $request->{$this->post_view_day} + 1;
		 } else {
			$value_post_view_day = 1;
		 }
		 if(is_numeric($request->{$this->post_view_week})) {
			$value_post_view_week = $request->{$this->post_view_week} + 1;
		 } else {
			$value_post_view_week = 1;
		 }
		 if(is_numeric($request->{$this->post_view_month})) {
			$value_post_view_month = $request->{$this->post_view_month} + 1;
		 } else {
			$value_post_view_month = 1;
		 }
		 
		 // Cập nhật số lượng view mới vào database
		 $wpdb->update(
			  $this->table_name,
			  [
				   $this->post_views => $value_post_views,
				   $this->post_view_day => $value_post_view_day,
				   $this->post_view_week => $value_post_view_week,
				   $this->post_view_month => $value_post_view_month
			  ],
			  ['slug' => $permalink]
		 );


		 // Nếu không tìm thấy permalink, trả về thông báo lỗi
		 return new WP_REST_Response(['message' => 'done', 'movie' => $value_post_view_day], 200);
	}
	
	public function cron_job_resert_view() {
		if (!wp_next_scheduled('resert_view_daily')) {
			wp_schedule_event(time(), 'daily', 'resert_view_daily');
		}

		if (!wp_next_scheduled('resert_view_week')) {
			wp_schedule_event(time(), 'weekly', 'resert_view_week');
		}

		if(!wp_next_scheduled('resert_view_month')) {
			wp_schedule_event(time(), 'monthly', 'resert_view_month');
		}
	}
	public function cron_job_reset_view_daily() {
		global $wpdb;
		// Reset cột post_view_day về 0
		$wpdb->query(
			 "UPDATE {$this->table_name} SET {$this->post_view_day} = 0"
		);
	}
	
	public function cron_job_reset_view_week() {
			global $wpdb;
			// Reset cột post_view_week về 0
			$wpdb->query(
				"UPDATE {$this->table_name} SET {$this->post_view_week} = 0"
			);
	}
	
	public function cron_job_reset_view_month() {
			global $wpdb;
			// Reset cột post_view_month về 0
			$wpdb->query(
				"UPDATE {$this->table_name} SET {$this->post_view_month} = 0"
			);
	}
  
};
// Khởi tạo class với các giá trị đầu vào tùy chỉnh



new WP_view_update_API('wp_pods_comics', 'post_view', 'views_day', 'views_week', 'views_month');
