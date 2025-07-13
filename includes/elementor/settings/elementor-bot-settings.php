<?php
namespace PayamentorIran\Elementor\Settings;

if (!defined('ABSPATH')) exit;

class Elementor_Bot_Settings {

    public function __construct() {
        add_action('elementor/element/form/section_buttons/after_section_end', [$this, 'add_bot_controls_section'], 10, 2);
    }

    public function add_bot_controls_section($element, $args) {
        $element->start_controls_section(
            'bot_iran_form_panel_section',
            [
                'label' => esc_html__('پیامنتور | اطلاع‌رسانی ربات پیامرسان', PAYAMENTOR_IRAN_DOMAIN),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $element->add_control(
            'important_note_bot_iran',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => esc_html__('اطلاع رسانی فرم از طریق پیامرسان فقط برای مدیر امکان پذیر است.', PAYAMENTOR_IRAN_DOMAIN),
                'content_classes' => 'elementor-panel-notice elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $element->add_control(
            'bot_iran_form_list',
            [
                'label' => esc_html__('پیامرسان را انتخاب کنید', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true, // امکان انتخاب چند گزینه
                'options' => [
                    'bale-ai' => 'پیامرسان بله',
                    'telegram' => 'تلگرام',
                ],
            ]
        );

        // تنظیمات مربوط به پیام‌رسان بله
        $element->add_control(
            'bot_iran_bale_ai_token',
            [
                'label' => esc_html__('توکن بات بله', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_form_list' => 'bale-ai', // نمایش فقط اگر بله انتخاب شده باشد
                ],
                'description' => esc_html__('توکن (شناسه) بات بله را وارد کنید', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_bale_ai_userid',
            [
                'label' => esc_html__('شناسه کاربری بله', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_form_list' => 'bale-ai', // نمایش فقط اگر بله انتخاب شده باشد
                ],
                'description' => esc_html__('شناسه کاربری (user id) حساب کاربری بله مدیر را وارد کنید', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        // تنظیمات مربوط به تلگرام
        $element->add_control(
            'bot_iran_telegram_token',
            [
                'label' => esc_html__('توکن بات تلگرام', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_form_list' => 'telegram', // نمایش فقط اگر تلگرام انتخاب شده باشد
                ],
                'description' => esc_html__('توکن (شناسه) بات تلگرام را وارد کنید', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_telegram_userid',
            [
                'label' => esc_html__('شناسه کاربری تلگرام', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_form_list' => 'telegram', // نمایش فقط اگر تلگرام انتخاب شده باشد
                ],
                'description' => esc_html__('شناسه کاربری (user id) حساب کاربری تلگرام مدیر را وارد کنید', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        // تنظیمات مشترک
        $element->add_control(
            'bot_iran_internal_host_switch',
            [
                'label' => esc_html__('فعال‌سازی پراکسی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'bot_iran_form_list' => 'telegram', // نمایش فقط اگر تلگرام انتخاب شده باشد
                ],
                'description' => esc_html__('فعال‌سازی پراکسی تلگرام برای هاست‌های داخلی', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_proxy_url',
            [
                'label' => esc_html__('آدرس پراکسی گوگل اسکریپت', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_internal_host_switch' => 'yes',
                    'bot_iran_form_list' => 'telegram', // نمایش فقط اگر تلگرام انتخاب شده باشد
                ],
                'description' => esc_html__('آدرس پراکسی گوگل اسکریپت خود را وارد کنید', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_form_test_message',
            [
                'label' => esc_html__('تست ارسال پیام', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('در نسخه منتشر شده، غیرفعال است.', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_form_enable_text_message',
            [
                'label' => esc_html__('فعال سازی ارسال پیام متنی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('اگر فعال باشد، پیام متنی ارسال خواهد شد.', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_form_message',
            [
                'label' => esc_html__('متن پیام', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'label_block' => true,
                'dynamic' => ['active' => true],
                'condition' => [
                    'bot_iran_form_enable_text_message' => 'yes',
                ],
                'description' => esc_html__('متن پیام را بنویسید. برای استفاده از فیلدهای فرم، از کدکوتاه هر فیلد استفاده کنید. فایل راهنمای افزونه را مطالعه کنید.', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_form_enable_file_message',
            [
                'label' => esc_html__('فعال‌سازی ارسال فایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('اگر فعال باشد، امکان ارسال فایل به ربات فراهم می‌شود.', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'bot_iran_form_file_fields',
            [
                'label' => esc_html__('تنظیمات ارسال فایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'file_type',
                        'label' => esc_html__('نوع فایل', PAYAMENTOR_IRAN_DOMAIN),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                            'image' => esc_html__('تصویر', PAYAMENTOR_IRAN_DOMAIN),
                            'video' => esc_html__('ویدیو', PAYAMENTOR_IRAN_DOMAIN),
                            'audio' => esc_html__('صوتی', PAYAMENTOR_IRAN_DOMAIN),
                            'document' => esc_html__('فایل عمومی', PAYAMENTOR_IRAN_DOMAIN),
                        ],
                        'default' => 'image',
                    ],
                    [
                        'name' => 'file_id_fields',
                        'label' => esc_html__('ایدی فیلد فرم', PAYAMENTOR_IRAN_DOMAIN),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'label_block' => true,
                        'dynamic' => ['active' => true],
                    ],
                    [
                        'name' => 'file_caption',
                        'label' => esc_html__('کپشن فایل', PAYAMENTOR_IRAN_DOMAIN),
                        'type' => \Elementor\Controls_Manager::TEXTAREA,
                        'label_block' => true,
                        'dynamic' => ['active' => true],
                        'description' => esc_html__('برای فایل ارسال‌شده یک کپشن وارد کنید. اگر نیازی به کپشن نیست، می‌توانید خالی بگذارید.', PAYAMENTOR_IRAN_DOMAIN),
                    ]
                ],
                'default' => [
                    [
                        'file_type' => 'image',
                        'file_caption' => '',
                    ],
                ],
                'title_field' => '{{{ file_type }}}',
                'condition' => [
                    'bot_iran_form_enable_file_message' => 'yes',
                ],
            ]
        );

        $element->end_controls_section();
    }
}