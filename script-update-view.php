<!-- add code in footer -->
<?php $permalink = pods_v('last', 'url');
<?php global $api_url;  ?>
<script>
    document.addEventListener('DOMContentLoaded', function(e) {       
        var permalink = '<?=  $permalink; ?>';
          // Thiết lập các tùy chọn cho fetch
          const apiUrl = '<?=  $api_url; ?>';
          const options = {
              method: 'POST', // Sử dụng phương thức POST
              headers: {
                  'Content-Type': 'application/json', // Định dạng dữ liệu gửi đi là JSON
              },
              body: JSON.stringify({ permalink: permalink }) // Chuyển đổi đối tượng sang chuỗi JSON
          };

          fetch(apiUrl, options)
          .then(response => response.json()) // Chuyển đổi phản hồi thành JSON
          .then(data => {
              // Kiểm tra kết quả trả về từ API
              if (data && data.message) {
                  console.log(data.message); // In ra thông báo từ API
              }
          })
          .catch(error => {
              // Xử lý lỗi nếu có
              console.error('Error updating view:', error);
          });
    });
</script>
