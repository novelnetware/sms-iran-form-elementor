<?php
namespace PayamentorIran\Elementor\Settings;

if (!defined('ABSPATH')) exit; 

class Elementor_SMS_Verify_Settings {

    public function __construct() {
        add_action('elementor/element/form/section_buttons/after_section_end', [$this, 'add_sms_verify_controls_section'], 10, 2);
    }

    public function add_sms_verify_controls_section($element, $args) {
        $element->start_controls_section(
            PAYAMENTOR_IRAN_DOMAIN . '_sms_verification_controls_section',
            [
                'label' => esc_html__('پیامنتور | تایید شماره موبایل کاربر', PAYAMENTOR_IRAN_DOMAIN),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $element->add_control(
            'enable_sms_verification',
            [
                'label' => __('فعالسازی تأیید شماره موبایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('فعال', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => __('غیرفعال', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $element->add_control(
            'sms_verification_phone_field',
            [
                'label' => __('آیدی فیلد شماره موبایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true,
                'description' => __('آیدی فیلد شماره موبایل را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'condition' => [
                    'enable_sms_verification' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'sms_verification_field',
            [
                'label' => __('آیدی فیلد کد تأیید', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true,
                'description' => __('آیدی فیلد کد تأیید را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'condition' => [
                    'enable_sms_verification' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'sms_verification_empty_code_message',
            [
                'label' => __('پیام فیلد خالی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true,
                'default' => __('لطفاً کد تأیید را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'description' => __('در صورت خالی بودن فیلد کد تأیید، این پیام نمایش داده می‌شود.', PAYAMENTOR_IRAN_DOMAIN),
                'condition' => [
                    'enable_sms_verification' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'sms_verification_invalid_code_message',
            [
                'label' => __('پیام کد اشتباه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true,
                'default' => __('کد تأیید وارد شده اشتباه است.', PAYAMENTOR_IRAN_DOMAIN),
                'description' => __('در صورت اشتباه بودن کد تأیید، این پیام نمایش داده می‌شود.', PAYAMENTOR_IRAN_DOMAIN),
                'condition' => [
                    'enable_sms_verification' => 'yes',
                ],
            ]
        );
        
        $element->end_controls_section();
    }
}