<?php
namespace PayamentorIran;

defined('ABSPATH') || exit;

class Version_Manager {
    const CURRENT_VERSION      = '2.0.1'; // نسخه فعلی افزونه
    const BASE_VERSION         = '2.0.0'; // نسخه‌ای که ویژگی‌های جدید از آن شروع شده
    const NOTICE_DISPLAY_LIMIT = 4;

    public static function activate(): void {
        $current_version = get_option('payamentor_plugin_version');

        if (!$current_version) {
            update_option('payamentor_plugin_version', self::CURRENT_VERSION);
        }
    }

    public static function upgrade_completed($upgrader_object, $options): void {
        $our_plugin = plugin_basename(PAYAMENTOR_IRAN_FILE);

        // بررسی اینکه آیا عملیات روی افزونه ما انجام شده است
        $is_our_plugin = ($options['type'] === 'plugin' && isset($options['plugins']) && is_array($options['plugins']) && in_array($our_plugin, $options['plugins'], true));

        if ($is_our_plugin || $options['type'] === 'plugin') { // حتی اگر plugins نباشد، ادامه می‌دهیم
            $previous_version = get_option('payamentor_plugin_version');

            // اگر نسخه قبلی وجود ندارد یا کمتر از 2.0.0 است
            if (!$previous_version || version_compare($previous_version, self::BASE_VERSION, '<')) {
                set_transient('payamentor_plugin_updated', true, 0);
                update_option('payamentor_update_notice_count', 0);
            }

            update_option('payamentor_plugin_version', self::CURRENT_VERSION);
        }
    }
    
    public static function display_update_notice(): void {

        if (!current_user_can('administrator') || !get_transient('payamentor_plugin_updated')) {
            return;
        }

        $display_count = (int) get_option('payamentor_update_notice_count', 0);
        if ($display_count >= self::NOTICE_DISPLAY_LIMIT) {
            return;
        }

        $remaining_notices = self::NOTICE_DISPLAY_LIMIT - $display_count;
        ?>
        <div class="notice notice-warning is-dismissible payamentor-update-notice">
            <div class="payamentor-notice-content">
                <h3><?php _e('🚀 بروزرسانی بزرگ پیامنتور به نسخه 2.0.0', PAYAMENTOR_IRAN_DOMAIN); ?></h3>
                <div class="payamentor-notice-columns">
                    <div class="payamentor-notice-main">
                        <p><strong><?php _e('توجه فوری:', PAYAMENTOR_IRAN_DOMAIN); ?></strong><br>
                            <?php _e('با توجه به تغییرات اساسی در نسخه جدید، <strong>تمامی فرم‌های پیامکی و اطلاع رسانی ربات‌ها نیاز به تنظیم مجدد دارند</strong> تا پیام‌ها به درستی ارسال شوند.', PAYAMENTOR_IRAN_DOMAIN); ?></p>
                        <div class="payamentor-notice-alert">
                            <h4><?php _e('📌 اقدامات ضروری:', PAYAMENTOR_IRAN_DOMAIN); ?></h4>
                            <ul>
                                <li><?php _e('تنظیم مجدد تمام فرم‌های پیامکی و اطلاع رسانی ربات‌ها در المنتور', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                                <li><?php _e('فعال‌سازی گزینه «تایید شماره موبایل» در صورت نیاز', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                                <li><?php _e('مطالعه راهنمای جدید در پوشه افزونه', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                            </ul>
                        </div>
                        <p class="payamentor-notice-warning"><?php _e('⚠ در صورت عدم اقدام، ارسال پیامک‌ها متوقف خواهد شد!', PAYAMENTOR_IRAN_DOMAIN); ?></p>
                    </div>
                    <div class="payamentor-notice-features">
                        <h4><?php _e('✨ قابلیت‌های جدید:', PAYAMENTOR_IRAN_DOMAIN); ?></h4>
                        <ul>
                            <li><?php _e('• <strong>سیستم تایید شماره موبایل</strong> با کد یکبار مصرف', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                            <li><?php _e('پشتیبانی از سامانه‌های پیامکی بیشتر', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                            <li><?php _e('بهبود سرعت و امنیت ارسال پیامک‌ها', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                            <li><?php _e('بهبود سرعت و امنیت ارسال پیام به ربات‌ها', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                            <li><?php _e('رابط کاربری ساده‌تر برای تنظیمات', PAYAMENTOR_IRAN_DOMAIN); ?></li>
                        </ul>
                        <p class="payamentor-notice-footer">
                            <?php printf(
                                /* translators: %s: Guide URL */
                                __('برای راهنمایی کامل، فایل راهنما را مطالعه کنید.', PAYAMENTOR_IRAN_DOMAIN),
                            ); ?>
                        </p>
                    </div>
                </div>
                <p class="payamentor-notice-counter">
                    <?php printf(
                        /* translators: %1$d: Display count, %2$d: Limit, %3$d: Remaining */
                        __('این پیغام %1$d بار از %2$d بار ممکن نمایش داده شده است. (%3$d بار باقی مانده) اگر فرم‌ها را مجدد تنظیم کرده‌اید، لطفاً این پیام را نادیده بگیرید و ببندید.', PAYAMENTOR_IRAN_DOMAIN),
                        $display_count,
                        self::NOTICE_DISPLAY_LIMIT,
                        $remaining_notices
                    ); ?>
                </p>
            </div>
        </div>
        <style>
            .payamentor-update-notice { border-left: 4px solid #ffb900; padding: 15px 20px; position: relative; }
            .payamentor-notice-content { display: flex; flex-direction: column; gap: 15px; }
            .payamentor-notice-columns { display: flex; gap: 20px; }
            .payamentor-notice-main { flex: 1; }
            .payamentor-notice-features { flex: 1; background: #f0f9ff; padding: 15px; border-radius: 5px; border: 1px solid #d6d8db; }
            .payamentor-notice-alert { background: #fff8e5; padding: 10px 15px; border-radius: 4px; margin: 10px 0; }
            .payamentor-notice-warning { color: #d63638; font-weight: bold; }
            .payamentor-notice-counter { color: #555; font-size: 13px; margin-bottom: 5px; }
            .payamentor-notice-footer { font-size: 14px; margin-bottom: 0; }
        </style>
        <script>
            jQuery(document).on('click', '.payamentor-update-notice .notice-dismiss', function() {
                jQuery.post(ajaxurl, {
                    action: 'payamentor_dismiss_update_notice',
                    nonce: '<?php echo wp_create_nonce("payamentor_nonce"); ?>'
                });
            });
        </script>
        <?php
    }

    public static function dismiss_update_notice(): void {
        check_ajax_referer('payamentor_nonce', 'nonce');

        $display_count = (int) get_option('payamentor_update_notice_count', 0) + 1;
        update_option('payamentor_update_notice_count', $display_count);

        if ($display_count >= self::NOTICE_DISPLAY_LIMIT) {
            delete_transient('payamentor_plugin_updated');
        }

        wp_die();
    }
}