<?php
namespace PayamentorIran\Elementor\Settings;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

if (!defined('ABSPATH')) exit;

class Elementor_SMS_Verification_Button_Styles_Controls {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_filter('elementor/element/form/section_messages_style/after_section_end', [$this, 'elementor_form_inject_sms_verification_btn_styles_controls'], 10, 2);
    }


    public function elementor_form_inject_sms_verification_btn_styles_controls($element, $args) {

        $element->start_controls_section(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_styles_section',
            [
                'label' => __('پیامنتور | استایل دکمه ارسال کد', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Normal State
        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_normal_heading',
            [
                'label' => __('حالت عادی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_typography',
                'selector' => '{{WRAPPER}} .sms-verification-input',
                'label' => __('تایپوگرافی', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_text_color',
            [
                'label' => __('رنگ متن', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_background_color',
            [
                'label' => __('رنگ پس‌زمینه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_border',
                'selector' => '{{WRAPPER}} .sms-verification-input',
                'fields_options' => [
                    'border' => [
                        'label' => __('نوع حاشیه', PAYAMENTOR_IRAN_DOMAIN),
                    ],
                    'width' => [
                        'label' => __('عرض حاشیه', PAYAMENTOR_IRAN_DOMAIN),
                    ],
                    'color' => [
                        'label' => __('رنگ حاشیه', PAYAMENTOR_IRAN_DOMAIN),
                    ],
                ],
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_border_radius',
            [
                'label' => __('شعاع حاشیه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_box_shadow',
                'selector' => '{{WRAPPER}} .sms-verification-input',
            ]
        );

        $element->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_text_shadow',
                'selector' => '{{WRAPPER}} .sms-verification-input',
            ]
        );

        $element->add_responsive_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_padding',
            [
                'label' => __('فاصله داخلی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $element->add_responsive_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_margin',
            [
                'label' => __('فاصله خارجی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $element->add_responsive_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_align',
            [
                'label' => __('تراز', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('راست', PAYAMENTOR_IRAN_DOMAIN),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('وسط', PAYAMENTOR_IRAN_DOMAIN),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('چپ', PAYAMENTOR_IRAN_DOMAIN),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input' => 'text-align: {{VALUE}} !important;',
                ],
            ]
        );

        // Hover State
        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_heading',
            [
                'label' => __('حالت هاور', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_text_color',
            [
                'label' => __('رنگ متن', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_background_color',
            [
                'label' => __('رنگ پس‌زمینه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_border',
                'selector' => '{{WRAPPER}} .sms-verification-input:hover',
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_border_radius',
            [
                'label' => __('شعاع حاشیه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_hover_box_shadow',
                'selector' => '{{WRAPPER}} .sms-verification-input:hover',
            ]
        );

        // Disabled/Countdown State
        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_heading',
            [
                'label' => __('حالت غیرفعال/شمارش معکوس', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_typography',
                'selector' => '{{WRAPPER}} .sms-verification-input:disabled',
                'label' => __('تایپوگرافی', PAYAMENTOR_IRAN_DOMAIN),
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_text_color',
            [
                'label' => __('رنگ متن', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:disabled' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_background_color',
            [
                'label' => __('رنگ پس‌زمینه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:disabled' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_border',
                'selector' => '{{WRAPPER}} .sms-verification-input:disabled',
            ]
        );

        $element->add_control(
            PAYAMENTOR_IRAN_DOMAIN . 'sms_verification_btn_disabled_opacity',
            [
                'label' => __('شفافیت', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.1,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sms-verification-input:disabled' => 'opacity: {{SIZE}} !important;',
                ],
            ]
        );

        $element->end_controls_section();
    }
}

Elementor_SMS_Verification_Button_Styles_Controls::instance();
