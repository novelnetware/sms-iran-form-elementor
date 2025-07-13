<?php
namespace PayamentorIran\Elementor\Settings;

if (!defined('ABSPATH')) exit; 

class Elementor_SMS_Settings {

    public function __construct() {
        add_action('elementor/element/form/section_buttons/after_section_end', [$this, 'add_sms_controls_section'], 10, 2);
    }

    public function add_sms_controls_section($element, $args) {
        $element->start_controls_section(
            PAYAMENTOR_IRAN_DOMAIN . '_sms_controls_section',
            [
                'label' => esc_html__('پیامنتور | اطلاع‌رسانی پیامکی ', PAYAMENTOR_IRAN_DOMAIN),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $element->add_control(
            'sms_iran_form_smspanel',
            [
                'label'       => esc_html__('سامانه پیامکی', PAYAMENTOR_IRAN_DOMAIN),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true, 'multiple'    => false,
                'options'     => [
                    // در نگاشت های فایل رجیستری سامانه ها استفاده شده است
                    'farazsms-com'     => esc_html__('فراز اس‌ام‌اس | farazsms.com', PAYAMENTOR_IRAN_DOMAIN),
                    'ippanel-com'      => esc_html__('آی‌پی پنل | ippanel.com', PAYAMENTOR_IRAN_DOMAIN),
                    'kavenegar-com'   => esc_html__('کاوه نگار | kavenegar.com', PAYAMENTOR_IRAN_DOMAIN),
                    'Melipayamak-com' => esc_html__('ملی پیامک | melipayamak.com', PAYAMENTOR_IRAN_DOMAIN),
                    'sms-ir'          => esc_html__('ایده پردازان | sms.ir', PAYAMENTOR_IRAN_DOMAIN),
                    'msgway-com'      => esc_html__('راه پیام | msgway.com', PAYAMENTOR_IRAN_DOMAIN),
                    'Asanak-com'      => esc_html__('آسانک | asanak.com', PAYAMENTOR_IRAN_DOMAIN),
                    'Sunwaysms-com'   => esc_html__('راه آفتاب | sunwaysms.com', PAYAMENTOR_IRAN_DOMAIN),
                    'Farapayamak.IR'  => esc_html__('فراپیامک | farapayamak.ir', PAYAMENTOR_IRAN_DOMAIN),
                    'modirpayamak-com' => esc_html__('مدیر پیامک | modirpayamak.com', PAYAMENTOR_IRAN_DOMAIN),
                    'maxsms-co'       => esc_html__('مکس ‌اس‌ام‌اس | maxsms.co', PAYAMENTOR_IRAN_DOMAIN),
                    'tabansms-com'    => esc_html__('تابان اس‌ام‌اس | tabansms.com', PAYAMENTOR_IRAN_DOMAIN),
                    'panelsmspro-ir'  => esc_html__('پنل اس‌ام‌اس پرو | panelsmspro.ir', PAYAMENTOR_IRAN_DOMAIN),
                ],
                'description' => esc_html__('سامانه پیامکی خود را انتخاب کنید.', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            'sms_iran_form_apikey',
            [
                'label' => esc_html__( 'کلید api', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_smspanel' => ['sms-ir', 'kavenegar-com', 'msgway-com']],
                'description' => esc_html__( 'مقدار api سامانه پیامکی خود را وارد کنید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_username',
            [
                'label' => esc_html__( 'نام کاربری', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_smspanel' => ['Asanak-com', 'Sunwaysms-com', 'farazsms-com', 'ippanel-com', 'Melipayamak-com', 'Farapayamak.IR', 'modirpayamak-com', 'maxsms-co', 'tabansms-com', 'panelsmspro-ir']],
                'description' => esc_html__( 'نام کاربری سامانه پیامکی خود را وارد کنید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_password',
            [
                'label' => esc_html__( 'رمز عبور', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_smspanel' => ['Asanak-com', 'Sunwaysms-com', 'farazsms-com', 'ippanel-com', 'Melipayamak-com', 'Farapayamak.IR', 'modirpayamak-com', 'maxsms-co', 'tabansms-com', 'panelsmspro-ir']],
                'description' => esc_html__( 'رمز سامانه پیامکی خود را وارد کنید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_from',
            [
                'label' => esc_html__( 'خط فرستنده', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'description' => esc_html__( 'شماره خط فرستنده پیامک را از سامانه پیامکی دریافت کنید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_test_message',
            [
                'label' => esc_html__( 'تست ارسال پیامک', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'yes',
                'description' => esc_html__( 'در نسخه منتشر شده غیرفعال است.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );

        $element->start_controls_tabs( 'style_tabs' );
        
        $element->start_controls_tab(
			'sms_iran_form_user_tab',
			[
				'label' => esc_html__( 'پیامک کاربر', 'sms-iran-form-elementor' ),
			]
		);

        $element->add_control(
            'sms_iran_form_show_user',
            [
                'label' => esc_html__( 'ارسال به کاربر', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'yes',
                'description' => esc_html__( 'برای ارسال پیامک به کاربر باید فعال باشد.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_to_user',
            [
                'label' => esc_html__( 'شماره موبایل کاربر', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_show_user' => 'yes'],
                'description' => esc_html__( 'شناسه (ID) فیلد شماره کاربر را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_user_pattern',
            [
                'label' => esc_html__( 'ارسال پترن (سریع)', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'yes',
                'condition' => ['sms_iran_form_show_user' => 'yes'],
                'description' => esc_html__( 'در صورت غیرفعال بودن پیامک به صورت ساده ارسال می شود.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_user_voice',
            [
                'label' => esc_html__( 'ارسال پیام صوتی', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'no',
                'condition' => ['sms_iran_form_smspanel' => ['kavenegar-com'], 'sms_iran_form_user_pattern' => 'yes', 'sms_iran_form_show_user' => 'yes'],
                'description' => esc_html__( 'فایل راهنمای افزونه را مطالعه نمایید.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_message_user',
            [
                'label' => esc_html__( 'متن پیامک', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 10, 'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_user_pattern!' => 'yes', 'sms_iran_form_show_user' => 'yes'],
                'description' => esc_html__( 'متن پیامک ارسالی را بنویسید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_user_code_pattern',
            [
                'label' => esc_html__( 'کد پترن', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_user_pattern' => 'yes', 'sms_iran_form_show_user' => 'yes'],
                'description' => esc_html__( 'کد پترن را از سامانه پیامکی دریافت کنید.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_user_list_pattern',
            [
                'label' => esc_html__( 'تنظیمات پترن', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'sms_iran_form_user_key_pattern',
                        'label' => esc_html__( 'کلید/متغییر  پترن', PAYAMENTOR_IRAN_DOMAIN ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                    ],
                    [
                        'name' => 'sms_iran_form_user_value_pattern',
                        'label' => esc_html__( 'مقدار پترن', PAYAMENTOR_IRAN_DOMAIN ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                    ],
                ],
                'default' => [['sms_iran_form_user_key_pattern' => 'code', 'sms_iran_form_user_value_pattern' => 'id']],
                'condition' => ['sms_iran_form_user_pattern' => 'yes', 'sms_iran_form_show_user' => 'yes'],
                // 'title_field' => '{{{ sms_iran_form_user_key_pattern }}}',
            ]
        );
        
        $element->end_controls_tab();

        $element->start_controls_tab(
			'sms_iran_form_manager_tab',
			[
				'label' => esc_html__( 'پیامک مدیر', 'sms-iran-form-elementor' ),
			]
		);

        $element->add_control(
            'sms_iran_form_show_manager',
            [
                'label' => esc_html__( 'ارسال به مدیر', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'yes',
                'description' => esc_html__( 'برای ارسال پیامک به مدیر باید فعال باشد.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_to_manager',
            [
                'label' => esc_html__( 'شماره موبایل مدیر', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_show_manager' => 'yes'],
                'description' => esc_html__( 'شماره مدیران را با , جدا کنید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_manager_pattern',
            [
                'label' => esc_html__( 'ارسال پترن (سریع)', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'yes',
                'condition' => ['sms_iran_form_show_manager' => 'yes'],
                'description' => esc_html__( 'در صورت غیرفعال بودن پیامک به صورت ساده ارسال می شود.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_manager_voice',
            [
                'label' => esc_html__( 'ارسال پیام صوتی', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('بله', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => esc_html__('خیر', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'no',
                'condition' => ['sms_iran_form_smspanel' => ['kavenegar-com'], 'sms_iran_form_manager_pattern' => 'yes', 'sms_iran_form_show_manager' => 'yes'],
                'description' => esc_html__( 'در صورت غیرفعال بودن پیامک به صورت ساده ارسال می شود.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_message_manager',
            [
                'label' => esc_html__( 'متن پیامک', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 10, 'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_manager_pattern!' => 'yes', 'sms_iran_form_show_manager' => 'yes'],
                'description' => esc_html__( 'متن پیامک ارسالی را بنویسید', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_manager_code_pattern',
            [
                'label' => esc_html__( 'کد پترن', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                'condition' => ['sms_iran_form_manager_pattern' => 'yes', 'sms_iran_form_show_manager' => 'yes'],
                'description' => esc_html__( 'کد پترن را از سامانه پیامکی دریافت کنید.', PAYAMENTOR_IRAN_DOMAIN ),
            ]
        );
        
        $element->add_control(
            'sms_iran_form_manager_list_pattern',
            [
                'label' => esc_html__( 'تنظیمات پترن', PAYAMENTOR_IRAN_DOMAIN ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'sms_iran_form_manager_key_pattern',
                        'label' => esc_html__( 'کلید/متغییر  پترن', PAYAMENTOR_IRAN_DOMAIN ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                    ],
                    [
                        'name' => 'sms_iran_form_manager_value_pattern',
                        'label' => esc_html__( 'مقدار پترن', PAYAMENTOR_IRAN_DOMAIN ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'label_block' => true, 'dynamic' => ['active' => true], 'ai' => ['active' => false],
                    ],
                ],
                'default' => [['sms_iran_form_manager_key_pattern' => 'code', 'sms_iran_form_manager_value_pattern' => 'id']],
                'condition' => ['sms_iran_form_manager_pattern' => 'yes', 'sms_iran_form_show_manager' => 'yes'],
            ]
        );
        
        $element->end_controls_tab();
        $element->end_controls_tabs();
        

        $element->end_controls_section();
    }
}