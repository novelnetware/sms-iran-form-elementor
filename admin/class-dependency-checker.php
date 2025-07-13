<?php
namespace PayamentorIran;

defined('ABSPATH') || exit;

class Dependency_Checker {
    public static function check(): void {
        $required_plugins = apply_filters('payamentor_required_plugins', [
            'elementor' => [
                'name'       => __('Elementor', PAYAMENTOR_IRAN_DOMAIN),
                'plugin_file' => 'elementor/elementor.php',
                'is_active'  => is_plugin_active('elementor/elementor.php'),
            ],
            'elementor-pro' => [
                'name'       => __('Elementor Pro', PAYAMENTOR_IRAN_DOMAIN),
                'plugin_file' => 'elementor-pro/elementor-pro.php',
                'is_active'  => is_plugin_active('elementor-pro/elementor-pro.php'),
            ],
        ]);

        $missing_plugins = [];
        foreach ($required_plugins as $plugin) {
            if (!$plugin['is_active']) {
                $missing_plugins[] = $plugin['name'];
            }
        }

        if (!empty($missing_plugins)) {
            self::display_notice($missing_plugins);
        }
    }
    
    private static function display_notice(array $missing_plugins): void {
        add_action('admin_notices', function () use ($missing_plugins) {
            $plugins_list = implode(' و ', $missing_plugins);
            $message = sprintf(
                /* translators: %s: List of missing plugins */
                __('پیامنتور برای عملکرد صحیح نیاز به پلاگین‌ %s دارد. لطفاً این پلاگین‌ را نصب و فعال نمایید.', PAYAMENTOR_IRAN_DOMAIN),
                $plugins_list
            );

            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($message)
            );
        });
    }
}