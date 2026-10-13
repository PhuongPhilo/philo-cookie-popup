<?php
/**
 * Plugin Name: Philo Cookie Popup Elementor
 * Description: Plugin hiển thị thông báo Cookie có hỗ trợ widget cho Elementor.
 * Version: 1.0.2
 * Author: Philo Creative
 * Text Domain: philo-cookie
 * Domain Path: /languages
 */

// Ngăn chặn truy cập trực tiếp vào file này (Bảo mật WP)
if (!defined('ABSPATH')) {
    exit;
}

define('PHILO_COOKIE_VERSION', '1.0.2');

// Load Text Domain để hỗ trợ đa ngôn ngữ
add_action('plugins_loaded', 'philo_cookie_load_textdomain');
function philo_cookie_load_textdomain() {
    load_plugin_textdomain(
        'philo-cookie',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

// Hàm này dùng để nạp CSS và JS vào giao diện website
function philo_cookie_enqueue_scripts()
{
    // Nạp file CSS
    wp_enqueue_style('philo-cookie-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), PHILO_COOKIE_VERSION);

    // Nạp file JS (để ở footer)
    wp_enqueue_script('philo-cookie-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array(), PHILO_COOKIE_VERSION, true);
}
// Móc (Hook) hàm trên vào WordPress
add_action('wp_enqueue_scripts', 'philo_cookie_enqueue_scripts');


// Hàm đăng ký Widget với Elementor
function register_philo_cookie_widget($widgets_manager)
{
    // Nhúng file chứa Widget
    require_once(__DIR__ . '/widgets/cookie-widget.php');

    // Khởi tạo Widget
    $widgets_manager->register(new \Philo_Cookie_Widget());
}
// Gọi hàm này khi Elementor đang load danh sách Widget
add_action('elementor/widgets/register', 'register_philo_cookie_widget');

// Nạp file quản lý Cài đặt & Hiển thị độc lập (Non-Elementor)
require_once(__DIR__ . '/philo-global-cookie.php');