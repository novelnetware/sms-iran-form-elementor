<?php
/**
 * Plugin Name: پیامنتور | پیامک ایرانی ویجت فرم المنتور
 * Description: اطلاع‌رسانی پیامکی ویجت فرم المنتور به کاربران و مدیران از طریق سامانه‌های پیامکی ایرانی | افزونه ایرانی اختصاصی راستچین
 * Plugin URI:  https://www.rtl-theme.com/author/alitat/products/
 * Version:     2.0.1
 * Author:      علی تات | راستچین
 * Author URI:  https://www.rtl-theme.com/author/alitat/products/
 * Text Domain: payamentor_iran
 * Domain Path: /languages
 */

namespace PayamentorIran;

defined('ABSPATH') || exit;

if (!defined('PAYAMENTOR_IRAN_FILE')) {
    define('PAYAMENTOR_IRAN_FILE', __FILE__);
}

if (!defined('PAYAMENTOR_IRAN_DIR')) {
    define('PAYAMENTOR_IRAN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('PAYAMENTOR_IRAN_URL')) {
    define('PAYAMENTOR_IRAN_URL', plugin_dir_url(__FILE__));
}

if (!defined('PAYAMENTOR_IRAN_DOMAIN')) {
    define('PAYAMENTOR_IRAN_DOMAIN', 'payamentor_iran');
}

require_once PAYAMENTOR_IRAN_DIR . 'admin/class-core.php';
add_action('plugins_loaded', [Core::class, 'init']);