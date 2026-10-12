<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class Philo_Cookie_Widget extends \Elementor\Widget_Base
{

    // ID của widget
    public function get_name()
    {
        return 'philo_cookie_widget';
    }

    // Tên hiển thị trong Elementor
    public function get_title()
    {
        return 'Philo Cookie Popup';
    }

    // Icon hiển thị trong bảng công cụ Elementor
    public function get_icon()
    {
        return 'eicon-info-circle';
    }

    // Nhóm Widget (để ở mục Cơ bản)
    public function get_categories()
    {
        return ['basic'];
    }

    // ĐĂNG KÝ CÁC TRƯỜNG CÀI ĐẶT (CONTROLS)
    protected function register_controls()
    {
        // ==============================
        // TAB NỘI DUNG
        // ==============================
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Nội dung Popup', 'philo-cookie'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'cookie_icon',
            [
                'label' => esc_html__('Icon/Logo (Tùy chọn)', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => '',
                ],
                'description' => esc_html__('Bỏ trống để dùng icon 🍪 mặc định.', 'philo-cookie'),
            ]
        );

        $this->add_control(
            'popup_title',
            [
                'label' => esc_html__('Tiêu đề', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Cookie & trải nghiệm',
            ]
        );

        $this->add_control(
            'popup_desc',
            [
                'label' => esc_html__('Mô tả ngắn', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Cookie giúp website hoạt động ổn định và hỗ trợ trải nghiệm mua sắm thuận tiện hơn.',
            ]
        );

        $this->add_control(
            'policy_text',
            [
                'label' => esc_html__('Chữ hiển thị link', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Chính sách thu thập và xử lý dữ liệu cá nhân',
            ]
        );

        $this->add_control(
            'policy_link',
            [
                'label' => esc_html__('Đường dẫn chính sách', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => 'https://your-link.com',
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // ==============================
        // TAB STYLE (KIỂU)
        // ==============================
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Màu sắc & Giao diện', 'philo-cookie'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => esc_html__('Màu chủ đạo (Nút & Switch)', 'philo-cookie'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#b91c1c',
                'selectors' => [
                    // Elementor sẽ tự động render CSS này ra fontend, thay {{VALUE}} bằng màu bạn chọn
                    '{{WRAPPER}} .cookie-popup .btn-primary' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} input:checked + .slider' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cookie-footer-link a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    // GIAO DIỆN HIỂN THỊ RA BÊN NGOÀI
    protected function render()
    {
        // Lấy tất cả các cài đặt người dùng đã nhập
        $settings = $this->get_settings_for_display();
        
        // Xử lý Icon
        $icon_html = '<span class="cookie-icon">🍪</span>';
        if (!empty($settings['cookie_icon']['url'])) {
            $icon_html = '<img src="' . esc_url($settings['cookie_icon']['url']) . '" alt="Cookie Icon" class="cookie-icon" style="width: 20px; height: 20px; object-fit: contain;">';
        }

        // Xử lý Link
        $target = $settings['policy_link']['is_external'] ? ' target="_blank"' : '';
        $nofollow = $settings['policy_link']['nofollow'] ? ' rel="nofollow"' : '';
        $link_url = !empty($settings['policy_link']['url']) ? esc_url($settings['policy_link']['url']) : '#';
        ?>
        <div class="cookie-popup" id="myCookiePopup">
            <!-- Header -->
            <div class="cookie-header" id="toggleCookieBody">
                <div class="cookie-title">
                    <?php echo $icon_html; ?>
                    <span class="cookie-text"><?php echo esc_html($settings['popup_title']); ?></span>
                </div>
                <svg class="cookie-toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>

            <!-- Body -->
            <div class="cookie-body" id="cookieBody">
                <p class="cookie-desc">
                    <?php echo esc_html($settings['popup_desc']); ?>
                </p>

                <div class="cookie-list-title"><?php echo esc_html__('Danh sách Cookie', 'philo-cookie'); ?></div>
                <div class="cookie-warning">
                    <?php echo esc_html__('Việc từ chối một số loại cookie có thể ảnh hưởng đến hoạt động của một số chức năng trên website.', 'philo-cookie'); ?>
                </div>

                <div class="cookie-item">
                    <span class="cookie-item-text"><?php echo esc_html__('Cần thiết (luôn bật)', 'philo-cookie'); ?></span>
                    <label class="switch">
                        <input type="checkbox" id="cookieRequired" checked disabled>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="cookie-item">
                    <span class="cookie-item-text"><?php echo esc_html__('Nâng cao chất lượng & cá nhân hóa trải nghiệm', 'philo-cookie'); ?></span>
                    <label class="switch">
                        <input type="checkbox" id="cookieAnalytics" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="cookie-item">
                    <span class="cookie-item-text"><?php echo esc_html__('Thông tin sản phẩm, khuyến mại & quảng cáo phù hợp', 'philo-cookie'); ?></span>
                    <label class="switch">
                        <input type="checkbox" id="cookieMarketing" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="cookie-footer-link">
                    Tìm hiểu thêm tại <a href="<?php echo $link_url; ?>"<?php echo $target . $nofollow; ?>><?php echo esc_html($settings['policy_text']); ?></a>
                </div>

                <div class="cookie-actions">
                    <button type="button" class="btn btn-outline" id="btnCookieReject"><?php echo esc_html__('Từ chối', 'philo-cookie'); ?></button>
                    <button type="button" class="btn btn-outline" id="btnCookieSave"><?php echo esc_html__('Lưu tùy chọn', 'philo-cookie'); ?></button>
                    <button type="button" class="btn btn-primary" id="btnCookieAcceptAll"><?php echo esc_html__('Đồng ý', 'philo-cookie'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
}