<?php
namespace PayamentorIran\Elementor\Actions;

use ElementorPro\Modules\Forms\Classes\Form_Record;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit;

class Elementor_SMS_Verify_Actions {

    public function __construct() {
        add_action('elementor_pro/forms/process', [$this, 'handle_verification_code'], 10, 2);
        add_action('wp_ajax_verify_sms_code', [$this, 'ajax_verify_sms_code']);
        add_action('wp_ajax_nopriv_verify_sms_code', [$this, 'ajax_verify_sms_code']);
    }

    public function handle_verification_code(Form_Record $record, $ajax_handler) {
        $form_settings = $record->get('form_settings');
        
        // Check if SMS verification is enabled
        if (empty($form_settings['enable_sms_verification']) || $form_settings['enable_sms_verification'] !== 'yes') {
            return;
        }
        
        // Check if required fields are set
        if (empty($form_settings['sms_verification_field']) || empty($form_settings['sms_verification_phone_field'])) {
            return;
        }

        $phone_field = $form_settings['sms_verification_phone_field'];
        $code_field = $form_settings['sms_verification_field'];
        $fields = $record->get('fields');
        
        // Check if phone number is provided
        if (empty($fields[$phone_field]['value'])) {
            $ajax_handler->add_error(
                $phone_field,
                $form_settings['sms_verification_empty_phone_message'] ?? __('لطفاً شماره موبایل را وارد کنید.', 'payamentor-iran')
            );
            return;
        }

        // Check if verification code is provided
        if (empty($fields[$code_field]['value'])) {
            $ajax_handler->add_error(
                $code_field,
                $form_settings['sms_verification_empty_code_message'] ?? __('لطفاً کد تأیید را وارد کنید.', 'payamentor-iran')
            );
            return;
        }

        $phone_number = sanitize_text_field($fields[$phone_field]['value']);
        $entered_code = sanitize_text_field($fields[$code_field]['value']);

        $transient_key = 'sms_verification_' . $record->get_form_settings('id') . '_' . md5($phone_number);
        $stored_data = get_transient($transient_key);

        if (!$stored_data) {
            $ajax_handler->add_error(
                $code_field,
                $form_settings['sms_verification_expired_code_message'] ?? __('کد تأیید منقضی شده است. لطفاً کد جدید دریافت کنید.', 'payamentor-iran')
            );
            return;
        }

        if ($stored_data['code'] !== $entered_code) {
            $ajax_handler->add_error(
                $code_field,
                $form_settings['sms_verification_invalid_code_message'] ?? __('کد تأیید وارد شده اشتباه است.', 'payamentor-iran')
            );
            return;
        }

        // If we reach here, verification is successful
        // Log the successful verification
        $this->log_verification($phone_number, $entered_code, true);
        
        // Clear the transient after successful verification
        delete_transient($transient_key);
    }

    public function ajax_verify_sms_code() {
        if (!check_ajax_referer('payamentor_nonce', 'security', false)) {
            wp_send_json_error(['message' => __('خطای امنیتی!', 'payamentor-iran')], 403);
            return;
        }

        $phone_number = sanitize_text_field($_POST['phone_number'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');
        $form_id = sanitize_text_field($_POST['form_id'] ?? '');

        if (empty($phone_number) || empty($code) || empty($form_id)) {
            wp_send_json_error(['message' => __('اطلاعات ناقص است!', 'payamentor-iran')], 400);
            return;
        }

        $transient_key = 'sms_verification_' . $form_id . '_' . md5($phone_number);
        $stored_data = get_transient($transient_key);

        if (!$stored_data) {
            wp_send_json_error([
                'message' => __('کد منقضی شده است. لطفاً کد جدید دریافت کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'expired' => true
            ], 400);
            return;
        }

        if ($stored_data['code'] === $code) {
            // Log successful verification
            $this->log_verification($phone_number, $code, true);
            
            wp_send_json_success([
                'message' => __('کد تأیید با موفقیت تأیید شد.', PAYAMENTOR_IRAN_DOMAIN),
                'remaining_time' => $stored_data['expires'] - time()
            ]);
        } else {
            // Log failed attempt
            $this->log_verification($phone_number, $code, false);
            
            wp_send_json_error([
                'message' => get_option('payamentor_invalid_code_message', __('کد اشتباه است! تا پایان زمان مهلت دارید یا کد جدید دریافت کنید.', 'payamentor-iran')),
                'remaining_time' => $stored_data['expires'] - time()
            ], 400);
        }
    }

    private function log_verification($phone, $code, $success) {
        $log_message = sprintf(
            '[SMS Verification] Phone: %s | Code: %s | Status: %s | Time: %s',
            $phone,
            $code,
            $success ? 'Success' : 'Failed',
            date('Y-m-d H:i:s')
        );
        error_log($log_message);
    }
}