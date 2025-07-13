<?php
namespace PayamentorIran\Elementor\Actions;

use PayamentorIran\SMS\Registery\SMSGatewayRegistry;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

if (!defined('ABSPATH')) exit;

class Elementor_SMS_Notification_Actions {

    private $smsSettings;
    private $smsGateway;

    public function __construct() {
        add_action('elementor_pro/forms/process', [$this, 'handle_sms_notification'], 10, 2);
    }

    public function handle_sms_notification(Form_Record $record, Ajax_Handler $ajax_handler) {
        $this->smsSettings = $record->get('form_settings');
    
        // دریافت تنظیمات سامانه پیامکی
        $sms_panel = $this->smsSettings['sms_iran_form_smspanel'] ?? '';
        $username = $this->smsSettings['sms_iran_form_username'] ?? '';
        $password = $this->smsSettings['sms_iran_form_password'] ?? '';
        $from = $this->smsSettings['sms_iran_form_from'] ?? '';
        $api_key = $this->smsSettings['sms_iran_form_apikey'] ?? '';
    
        // دریافت شی سامانه پیامکی از رجیستری
        require_once PAYAMENTOR_IRAN_DIR . 'includes/sms-gateways/registery/class-payamentor-smsgateway-registry.php';
        $registry = SMSGatewayRegistry::getInstance();
        
        // تنظیمات بر اساس سامانه پیامکی انتخاب شده
        $config = [];
        if (in_array($sms_panel, ['kavenegar-com', 'sms-ir', 'msgway-com'])) {
            $config['apikey'] = $api_key;
        } else {
            $config['username'] = $username;
            $config['password'] = $password;
            $config['from'] = $from;
        }
    
        try {
            $this->smsGateway = $registry->getGateway($sms_panel, $config);
        } catch (\Exception $e) {
            return;
        }
    
        // دریافت فیلدهای فرم
        $fields = $this->extractFieldValues($record->get('fields'));
    
        // ارسال پیامک به کاربر و مدیر
        $this->handleUserSMS($fields);
        $this->handleManagerSMS($fields);
    }

    private function handleUserSMS(array $fields) {
        if (isset($this->smsSettings['sms_iran_form_show_user']) && $this->smsSettings['sms_iran_form_show_user'] === 'yes') {
            $to_user = $fields[$this->smsSettings['sms_iran_form_to_user']] ?? '';
    
            if (!empty($to_user)) {
                if (isset($this->smsSettings['sms_iran_form_user_pattern']) && $this->smsSettings['sms_iran_form_user_pattern'] === 'yes') {
                    $parametersPattern = $this->prepareUserPatternParameters($this->smsSettings['sms_iran_form_user_list_pattern'] ?? [], $fields);
                    if (method_exists($this->smsGateway, 'sendPatternSms')) {
                        $result = $this->smsGateway->sendPatternSms($to_user, $this->smsSettings['sms_iran_form_user_code_pattern'], $parametersPattern);
                    } else {
                        error_log('سامانه پیامکی انتخاب‌ شده از ارسال پیامک با الگو پشتیبانی نمی‌کند.');
                        return;
                    }

                } else {
                    $message = $this->replacePlaceholders($this->smsSettings['sms_iran_form_message_user'], $fields);
                    $result = $this->smsGateway->sendSms($to_user, $message);
                }
    
                if (is_string($result)) {
                    error_log('خطا در ارسال پیامک به کاربر: ' . $result);
                }
            } else {
                error_log('خطا: شماره موبایل کاربر وارد نشده است.');
            }
        }
    }
    
    private function handleManagerSMS(array $fields) {
        if (isset($this->smsSettings['sms_iran_form_show_manager']) && $this->smsSettings['sms_iran_form_show_manager'] === 'yes') {
            $to_manager = $this->smsSettings['sms_iran_form_to_manager'] ?? '';
            $manager_numbers = preg_split('/[\*,]/', $to_manager);
    
            foreach ($manager_numbers as $mobile) {
                if (!empty($mobile)) {
                    if (isset($this->smsSettings['sms_iran_form_manager_pattern']) && $this->smsSettings['sms_iran_form_manager_pattern'] === 'yes') {
                        // ارسال پیامک با الگو (پترن)
                        $parametersPattern = $this->prepareManagerPatternParameters($this->smsSettings['sms_iran_form_manager_list_pattern'] ?? [], $fields);
                        if (method_exists($this->smsGateway, 'sendPatternSms')) {
                            $result = $this->smsGateway->sendPatternSms($mobile, $this->smsSettings['sms_iran_form_manager_code_pattern'], $parametersPattern);
                        } else {
                            error_log('سامانه پیامکی انتخاب‌شده از ارسال پیامک با الگو پشتیبانی نمی‌کند.');
                            return;
                        }
                    } else {
                        // ارسال پیامک ساده
                        $message = $this->replacePlaceholders($this->smsSettings['sms_iran_form_message_manager'], $fields);
                        $result = $this->smsGateway->sendSms($mobile, $message);
                    }
    
                    if (is_string($result)) {
                        error_log('خطا در ارسال پیامک به مدیر (' . $mobile . '): ' . $result);
                    }
                }
            }
        }
    }

    private function prepareUserPatternParameters(array $patternList, array $fields): array {
        $parameters = [];
        foreach ($patternList as $item) {
            if (isset($item['sms_iran_form_user_key_pattern']) && isset($fields[$item['sms_iran_form_user_value_pattern']])) {
                $parameters[$item['sms_iran_form_user_key_pattern']] = $fields[$item['sms_iran_form_user_value_pattern']];
            }
        }
        return $parameters;
    }

    private function prepareManagerPatternParameters(array $patternList, array $fields): array {
        $parameters = [];
        foreach ($patternList as $item) {
            if (isset($item['sms_iran_form_manager_key_pattern']) && isset($fields[$item['sms_iran_form_manager_value_pattern']])) {
                $parameters[$item['sms_iran_form_manager_key_pattern']] = $fields[$item['sms_iran_form_manager_value_pattern']];
            }
        }
        return $parameters;
    }

    private function replacePlaceholders(string $message, array $fields): string {
        // تشخیص روش جایگزینی
        if (strpos($message, '[field id="') !== false) {
            // روش دوم: جایگزینی با استفاده از [field id="..."]
            $message = preg_replace_callback('/\[field id="([^"]+)"\]/', function($matches) use ($fields) {
                $fieldId = $matches[1]; // شناسه فیلد (مثلاً name یا phone)
                return $fields[$fieldId] ?? ''; // اگر فیلد وجود داشت، مقدار آن را برگردان
            }, $message);
        } elseif (strpos($message, '{{') !== false) {
            // روش سوم: جایگزینی با استفاده از {{...}}
            $message = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($fields) {
                $fieldId = $matches[1]; // شناسه فیلد (مثلاً name یا field_4dd9ae7)
                return $fields[$fieldId] ?? ''; // اگر فیلد وجود داشت، مقدار آن را برگردان
            }, $message);
        } else {
            // روش اول: جایگزینی مستقیم (مثلاً name و phone)
            foreach ($fields as $key => $value) {
                $message = str_replace($key, $value, $message);
            }
        }
    
        // لاگ‌گیری برای بررسی نتیجه
        error_log('متن پیام پس از جایگزینی: ' . $message);
    
        return $message;
    }

    private function extractFieldValues($rawFields): array {
        $fields = [];
        if (is_array($rawFields) || is_object($rawFields)) {
            foreach ($rawFields as $id => $field) {
                $fields[$id] = $field['value'] ?? '';
            }
        }
        return $fields;
    }

}