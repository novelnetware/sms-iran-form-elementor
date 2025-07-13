<?php
namespace PayamentorIran;

defined('ABSPATH') || exit;

class Core {
    
    private static $instance = null;

    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function init(): void {
        $instance = self::get_instance();
        $instance->load_dependencies();
        $instance->setup_hooks();
    }
    
    private function __construct() {
        $this->load_textdomain();
    }
    
    private function load_textdomain(): void {
        load_plugin_textdomain(
            PAYAMENTOR_IRAN_DOMAIN,
            false,
            basename(PAYAMENTOR_IRAN_DIR) . '/languages'
        );
    }

    private function load_dependencies(): void {
        require_once PAYAMENTOR_IRAN_DIR . 'admin/class-dependency-checker.php';
        require_once PAYAMENTOR_IRAN_DIR . 'admin/class-elementor-integration.php';
        require_once PAYAMENTOR_IRAN_DIR . 'admin/class-version-manager.php';
    }

    private function setup_hooks(): void {
        add_action('admin_init', [Dependency_Checker::class, 'check']);
        
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        if (is_plugin_active('elementor/elementor.php') && is_plugin_active('elementor-pro/elementor-pro.php')) {
            add_action('plugins_loaded', function() {
                $elementor_integration = new \PayamentorIran\Admin\Elementor_Integration(); // استفاده از فضای نام کامل
            }, 20);
        } 

        register_activation_hook(PAYAMENTOR_IRAN_FILE, [Version_Manager::class, 'activate']);
        add_action('upgrader_process_complete', [Version_Manager::class, 'upgrade_completed'], 10, 2);
        add_action('admin_notices', [Version_Manager::class, 'display_update_notice']);
        add_action('wp_ajax_payamentor_dismiss_update_notice', [Version_Manager::class, 'dismiss_update_notice']);
    }
}