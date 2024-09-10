<?php
// count view 
class WP_view_update_API {
	private $table_name;
	private $post_views;
	private $post_view_day;
	private $post_view_week;
	private $post_view_month;
	private $api_url;

	// Constructor nhận các tham số đầu vào để thiết lập các thuộc tính
	public function __construct($api_url = '', $table_name = 'wp_pods_movie', $post_views = 'post_views', $post_view_day = 'post_view_day', $post_view_week = 'post_view_week', $post_view_month = 'post_view_month') {
		 // Gán giá trị đầu vào cho các thuộc tính của class
		 $this->table_name = $table_name;
		 $this->post_views = $post_views;
		 $this->post_view_day = $post_view_day;
		 $this->post_view_week = $post_view_week;
		 $this->post_view_month = $post_view_month;
		 $this->api_url = $api_url;

		// Đăng ký API endpoint khi khởi tạo class
		add_action('rest_api_init', [$this, 'register_api_endpoints']);
		add_action('reset_views_daily', [$this, 'reset_views_daily']);
		add_action('reset_views_week', [$this, 'cron_reset_views_week']);
		add_action('reset_views_month', [$this, 'cron_reset_views_month']);
		$this->cron_job_reset_view();
		add_action( 'after_setup_theme', [$this, 'api_url' ] );

	}

		// Đăng ký các endpoint cho WP REST API
		public function register_api_endpoints() {
			register_rest_route('post-api/v1', $this->api_url, [
				'methods' => 'POST',
				'callback' => [$this, 'update_post_view'],
				'permission_callback' => '__return_true', // Có thể thay đổi theo nhu cầu
			]);
		}
		public function api_url() {
			global $api_url;
			$api_url = home_url('wp-json/post-api/v1/'.$this->api_url );
		}

	// Hàm để cập nhật số lượng view của bài viết
	public function update_post_view($request) {
		 global $wpdb;

		 // Lấy permalink từ request
		 $permalink = sanitize_text_field($request['permalink']); // Đảm bảo rằng permalink được xử lý an toàn

		 // Lấy số lượng view hiện tại từ database
		 $sql = $wpdb->prepare(
			  "SELECT {$this->post_views}, {$this->post_view_day}, {$this->post_view_week}, {$this->post_view_month} FROM {$this->table_name} WHERE permalink = %s",
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
			  ['permalink' => $permalink]
		 );


		 // Nếu không tìm thấy permalink, trả về thông báo lỗi
		 return new WP_REST_Response(['message' => 'done', 'movie' => $value_post_view_month], 200);
	}
	
	public function cron_job_reset_view() {
		if (!wp_next_scheduled('reset_views_daily')) {
			wp_schedule_event(time(), 'daily', 'reset_views_daily');
		}

		if (!wp_next_scheduled('reset_views_week')) {
			wp_schedule_event(time(), 'weekly', 'reset_views_week');
		}

		if(!wp_next_scheduled('reset_views_month')) {
			wp_schedule_event(time(), 'monthly', 'reset_views_month');
		}
	}
	public function reset_views_daily() {
		global $wpdb;
		// Reset cột post_view_day về 0
		$wpdb->query(
			 "UPDATE {$this->table_name} SET {$this->post_view_day} = 0"
		);
  }
  
  public function cron_reset_views_week() {
		global $wpdb;
		// Reset cột post_view_week về 0
		$wpdb->query(
			 "UPDATE {$this->table_name} SET {$this->post_view_week} = 0"
		);
  }
  
  public function cron_reset_views_month() {
		global $wpdb;
		// Reset cột post_view_month về 0
		$wpdb->query(
			 "UPDATE {$this->table_name} SET {$this->post_view_month} = 0"
		);
  }
  
};
