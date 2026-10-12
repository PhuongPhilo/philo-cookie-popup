<?php
if (!defined('ABSPATH')) exit;

// 1. Tạo Menu con trong mục "Cài đặt" (Settings) của WordPress
add_action('admin_menu', 'philo_cookie_add_settings_page');
function philo_cookie_add_settings_page() {
    add_options_page(
        'Philo Cookie Settings', 
        'Philo Cookie', 
        'manage_options', 
        'philo-cookie-settings', 
        'philo_cookie_render_settings_page'
    );
}

// 2. Đăng ký các trường dữ liệu
add_action('admin_init', 'philo_cookie_register_settings');
function philo_cookie_register_settings() {
    register_setting('philo_cookie_options_group', 'philo_cookie_settings');
}

// 3. Nạp thư viện Media Uploader của WordPress vào trang cài đặt
add_action('admin_enqueue_scripts', 'philo_cookie_admin_scripts');
function philo_cookie_admin_scripts($hook) {
    // Chỉ nạp thư viện ở đúng trang cài đặt của plugin này để tránh nặng admin
    if ($hook !== 'settings_page_philo-cookie-settings') {
        return;
    }
    wp_enqueue_media();
}

// 4. Xây dựng giao diện trang Cài đặt trong Admin
function philo_cookie_render_settings_page() {
    $settings = get_option('philo_cookie_settings', []);
    $enabled = $settings['enabled'] ?? '';
    $position = $settings['position'] ?? 'bottom-left';
    $primary_color = $settings['primary_color'] ?? '#b91c1c';
    $policy_url = $settings['policy_url'] ?? '#';
    $icon_url = $settings['icon_url'] ?? ''; // Thêm biến lấy URL icon
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Tùy chỉnh Philo Cookie Popup', 'philo-cookie'); ?></h1>
        <p><?php esc_html_e('Cài đặt này áp dụng cho các trang không sử dụng Elementor Widget.', 'philo-cookie'); ?></p>
        
        <form method="post" action="options.php">
            <?php settings_fields('philo_cookie_options_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Bật Popup toàn cầu', 'philo-cookie'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="philo_cookie_settings[enabled]" value="1" <?php checked(1, $enabled); ?> />
                            <?php esc_html_e('Hiển thị popup trên toàn bộ website', 'philo-cookie'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Vị trí hiển thị', 'philo-cookie'); ?></th>
                    <td>
                        <select name="philo_cookie_settings[position]">
                            <option value="bottom-left" <?php selected('bottom-left', $position); ?>><?php esc_html_e('Góc dưới bên trái', 'philo-cookie'); ?></option>
                            <option value="bottom-right" <?php selected('bottom-right', $position); ?>><?php esc_html_e('Góc dưới bên phải', 'philo-cookie'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Icon tùy chỉnh', 'philo-cookie'); ?></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <!-- Vùng hiển thị xem trước ảnh (Preview) -->
                            <img id="philo_cookie_icon_preview" src="<?php echo esc_url($icon_url); ?>" style="max-width: 40px; max-height: 40px; border-radius: 4px; display: <?php echo $icon_url ? 'block' : 'none'; ?>;" />
                            
                            <!-- Icon mặc định (Hiển thị khi không có ảnh) -->
                            <span id="philo_cookie_icon_default" style="font-size: 24px; display: <?php echo $icon_url ? 'none' : 'block'; ?>;">🍪</span>
                            
                            <!-- Input ẩn lưu URL ảnh -->
                            <input type="hidden" name="philo_cookie_settings[icon_url]" id="philo_cookie_icon_url" value="<?php echo esc_attr($icon_url); ?>" />
                            
                            <button type="button" class="button" id="philo_cookie_upload_btn"><?php esc_html_e('Chọn ảnh', 'philo-cookie'); ?></button>
                            <button type="button" class="button" id="philo_cookie_remove_btn" style="display: <?php echo $icon_url ? 'inline-block' : 'none'; ?>; color: #b32d2e; border-color: #b32d2e;"><?php esc_html_e('Xóa ảnh', 'philo-cookie'); ?></button>
                        </div>
                        <p class="description"><?php esc_html_e('Khuyên dùng ảnh PNG, WebP hoặc SVG có nền trong suốt, định dạng vuông (VD: 40x40px). Nếu để trống sẽ hiển thị icon bánh quy 🍪 mặc định.', 'philo-cookie'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Màu thương hiệu (Nút & Switch)', 'philo-cookie'); ?></th>
                    <td>
                        <input type="color" name="philo_cookie_settings[primary_color]" value="<?php echo esc_attr($primary_color); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Link Chính sách dữ liệu', 'philo-cookie'); ?></th>
                    <td>
                        <input type="url" name="philo_cookie_settings[policy_url]" value="<?php echo esc_url($policy_url); ?>" class="regular-text" placeholder="https://..." />
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Lưu cài đặt', 'philo-cookie')); ?>
        </form>
    </div>

    <!-- Script xử lý nút Upload Ảnh trong Admin -->
    <script>
    jQuery(document).ready(function($){
        var mediaUploader;

        $('#philo_cookie_upload_btn').click(function(e) {
            e.preventDefault();
            // Nếu uploader đã được khởi tạo rồi thì mở lại
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            // Khởi tạo thư viện Media
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Chọn Icon cho Popup Cookie',
                button: { text: 'Sử dụng Icon này' },
                multiple: false // Chỉ cho phép chọn 1 ảnh
            });
            // Sự kiện khi chọn ảnh xong
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#philo_cookie_icon_url').val(attachment.url);
                $('#philo_cookie_icon_preview').attr('src', attachment.url).show();
                $('#philo_cookie_icon_default').hide();
                $('#philo_cookie_remove_btn').show();
            });
            // Mở thư viện
            mediaUploader.open();
        });

        // Xóa ảnh (khôi phục về icon mặc định)
        $('#philo_cookie_remove_btn').click(function(e) {
            e.preventDefault();
            $('#philo_cookie_icon_url').val('');
            $('#philo_cookie_icon_preview').hide().attr('src', '');
            $('#philo_cookie_icon_default').show();
            $(this).hide();
        });
    });
    </script>
    <?php
}

// 5. Hiển thị Popup ở Footer nếu được bật
add_action('wp_footer', 'philo_cookie_render_global_popup');
function philo_cookie_render_global_popup() {
    $settings = get_option('philo_cookie_settings', []);
    
    // Nếu không bật, dừng luôn
    if (empty($settings['enabled'])) return;

    $primary_color = $settings['primary_color'] ?? '#b91c1c';
    $position = $settings['position'] ?? 'bottom-left';
    $policy_url = $settings['policy_url'] ?? '#';
    $icon_url = $settings['icon_url'] ?? ''; // Lấy URL Icon
    
    // Xử lý CSS động ghi đè lên style.css mặc định
    $pos_css = ($position === 'bottom-right') ? 'left: auto !important; right: 20px;' : '';
    
    echo "<style>
        #myCookiePopup { {$pos_css} }
        .cookie-popup .btn-primary { background-color: {$primary_color} !important; }
        .cookie-popup .btn-primary:hover { filter: brightness(0.9); }
        input:checked + .slider { background-color: {$primary_color} !important; }
        .cookie-footer-link a { color: {$primary_color} !important; }
    </style>";

    // In ra cục HTML
    ?>
    <div class="cookie-popup" id="myCookiePopup">
        <div class="cookie-header" id="toggleCookieBody">
            <div class="cookie-title">
                <?php if ($icon_url) : ?>
                    <!-- Hiển thị ảnh thay thế icon -->
                    <img src="<?php echo esc_url($icon_url); ?>" alt="Cookie Icon" class="cookie-icon" style="width: 20px; height: 20px; object-fit: contain;">
                <?php else : ?>
                    <!-- Icon mặc định -->
                    <span class="cookie-icon">🍪</span>
                <?php endif; ?>
                <span class="cookie-text">Cookie & trải nghiệm</span>
            </div>
            <svg class="cookie-toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
        <div class="cookie-body" id="cookieBody">
            <p class="cookie-desc">Cookie giúp website hoạt động ổn định và hỗ trợ trải nghiệm mua sắm thuận tiện hơn.</p>
            <div class="cookie-list-title">Danh sách Cookie</div>
            <div class="cookie-warning">Việc từ chối một số loại cookie có thể ảnh hưởng đến hoạt động của một số chức năng trên website.</div>
            
            <div class="cookie-item">
                <span class="cookie-item-text">Cần thiết (luôn bật)</span>
                <label class="switch"><input type="checkbox" id="cookieRequired" checked disabled><span class="slider"></span></label>
            </div>
            <div class="cookie-item">
                <span class="cookie-item-text">Nâng cao chất lượng & cá nhân hóa</span>
                <label class="switch"><input type="checkbox" id="cookieAnalytics" checked><span class="slider"></span></label>
            </div>
            <div class="cookie-item">
                <span class="cookie-item-text">Thông tin sản phẩm & quảng cáo phù hợp</span>
                <label class="switch"><input type="checkbox" id="cookieMarketing" checked><span class="slider"></span></label>
            </div>
            
            <div class="cookie-footer-link">
                Tìm hiểu thêm tại <a href="<?php echo esc_url($policy_url); ?>">Chính sách thu thập và xử lý dữ liệu cá nhân</a>
            </div>
            <div class="cookie-actions">
                <button type="button" class="btn btn-outline" id="btnCookieReject">Từ chối</button>
                <button type="button" class="btn btn-outline" id="btnCookieSave">Lưu tùy chọn</button>
                <button type="button" class="btn btn-primary" id="btnCookieAcceptAll">Đồng ý</button>
            </div>
        </div>
    </div>
    <?php
}