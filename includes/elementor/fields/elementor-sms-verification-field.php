<?php
namespace PayamentorIran\Elementor\Fields;

use PayamentorIran\SMS\Registery\SMSGatewayRegistry;
use ElementorPro\Plugin;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Fields\Field_Base;

if (!defined('ABSPATH')) exit;

class Elementor_SMS_Verification_Field extends Field_Base {

    public $field_type = 'sms_verification';
    public $depended_scripts = ['sms-verification-script'];
    
    private $default_code_length = 5;
    private $default_expiry_time = 120; // seconds
    private $sms_result = null;

    public function __construct() {
        parent::__construct();
        $this->init_hooks();
        $this->init_ajax_handlers();
    }

    protected function init_hooks() {
        add_action('elementor/preview/init', [$this, 'editor_preview_footer']);
        add_action('wp_footer', [$this, 'add_countdown_script']);
    }

    protected function init_ajax_handlers() {
        add_action('wp_ajax_send_verification_code', [$this, 'send_verification_code']);
        add_action('wp_ajax_nopriv_send_verification_code', [$this, 'send_verification_code']);
    }

    public function get_type(): string {
        return $this->field_type;
    }

    public function get_name(): string {
        return __('دکمه ارسال کد تایید', PAYAMENTOR_IRAN_DOMAIN);
    }

    public function get_script_depends(): array {
        return $this->depended_scripts;
    }

    public function render($item, $item_index, $form): void {
        $form_id = $form->get_id();
        
        $code_length = !empty($item['code_length']) ? $item['code_length'] : $this->default_code_length;
        $expiry_seconds = !empty($item['expiry_time']) ? $item['expiry_time'] : $this->default_expiry_time;
        $phone_field_id = !empty($item['phone_field_id']) ? $item['phone_field_id'] : '';
        $validate_phone = isset($item['validate_phone']) ? $item['validate_phone'] : 'yes';
        $countdown_text = !empty($item['countdown_text']) ? $item['countdown_text'] : __('ثانیه باقی مانده', PAYAMENTOR_IRAN_DOMAIN);
        $countdown_position = !empty($item['countdown_position']) ? $item['countdown_position'] : 'after';
        $lock_phone_field = isset($item['lock_phone_field']) ? $item['lock_phone_field'] : 'no';
        
        $security_token = wp_generate_uuid4();
        set_transient('sms_token_' . $security_token, [
            'form_id' => $form_id,
            'code_length' => $code_length,
            'expiry_seconds' => $expiry_seconds,
            'phone_field' => $phone_field_id,
            'validate_phone' => $validate_phone,
            'countdown_text' => $countdown_text,
            'countdown_position' => $countdown_position,
            'lock_phone_field' => $lock_phone_field,
            'empty_phone_message' => $item['empty_phone_message'] ?? __('لطفاً شماره موبایل را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
            'invalid_phone_message' => $item['invalid_phone_message'] ?? __('شماره موبایل معتبر نیست. لطفاً شماره را به صورت 09123456789 وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
            'sms_verification_sms_provider' => $item['sms_verification_sms_provider'] ?? '',
            'sms_verification_api_key' => $item['sms_verification_api_key'] ?? '',
            'sms_verification_username' => $item['sms_verification_username'] ?? '',
            'sms_verification_password' => $item['sms_verification_password'] ?? '',
            'sms_verification_sender_number' => $item['sms_verification_sender_number'] ?? '',
            'sms_verification_pattern_code' => $item['sms_verification_pattern_code'] ?? '',
            'sms_verification_pattern_text' => $item['sms_verification_pattern_text'] ?? '',
        ], 3600);
        
        $form->add_render_attribute('input' . $item_index, [
            'class' => 'elementor-field-textual sms-verification-input',
            'type' => 'button',
            'value' => $item['button_text'] ?? __('ارسال کد تایید', PAYAMENTOR_IRAN_DOMAIN),
            'data-token' => $security_token,
            'data-phone-field' => $phone_field_id,
            'data-form-id' => $form_id,
            'data-validate-phone' => $validate_phone,
            'data-countdown-text' => $countdown_text,
            'data-countdown-position' => $countdown_position,
            'data-lock-phone-field' => $lock_phone_field,
            'data-empty-phone-message' => $item['empty_phone_message'] ?? __('لطفاً شماره موبایل را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
            'data-invalid-phone-message' => $item['invalid_phone_message'] ?? __('شماره موبایل معتبر نیست. لطفاً شماره را به صورت 09123456789 وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
            'readonly' => 'readonly',
            'onfocus' => 'this.blur();',
            'style' => 'cursor: pointer;'
        ]);

        echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . ' />';
    }

    public function send_verification_code() {
        if (!check_ajax_referer('payamentor_nonce', 'security', false)) {
            wp_send_json_error([
                'message' => __('خطای امنیتی رخ داده است.', PAYAMENTOR_IRAN_DOMAIN),
                'type' => 'security_error'
            ], 403);
        }

        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        $security_token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        
        if (empty($security_token)) {
            wp_send_json_error([
                'message' => __('توکن امنیتی نامعتبر است.', PAYAMENTOR_IRAN_DOMAIN),
                'type' => 'invalid_token'
            ], 403);
        }

        $token_data = get_transient('sms_token_' . $security_token);
        if (!$token_data) {
            wp_send_json_error([
                'message' => __('خطای امنیتی رخ داده است.', PAYAMENTOR_IRAN_DOMAIN),
                'type' => 'invalid_token_data'
            ], 403);
        }

        // Prepare response data
        $response_data = [
            'empty_phone_message' => $token_data['empty_phone_message'],
            'invalid_phone_message' => $token_data['invalid_phone_message'],
            'validate_phone' => $token_data['validate_phone']
        ];

        // Validate empty phone
        if (empty($phone_number)) {
            wp_send_json_error([
                'message' => $response_data['empty_phone_message'],
                'type' => 'empty_phone',
                'data' => $response_data
            ], 400);
        }

        // Validate phone format if enabled
        if ($token_data['validate_phone'] === 'yes' && !$this->validate_iranian_phone($phone_number)) {
            wp_send_json_error([
                'message' => $response_data['invalid_phone_message'],
                'type' => 'invalid_phone',
                'data' => $response_data
            ], 400);
        }

        // Generate and store verification code
        $form_id = $token_data['form_id'];
        $code_length = $token_data['code_length'];
        $expiry_seconds = $token_data['expiry_seconds'];
        $countdown_text = $token_data['countdown_text'];
        $countdown_position = $token_data['countdown_position'];
        $lock_phone_field = $token_data['lock_phone_field'];

        $transient_key = 'sms_verification_' . $form_id . '_' . md5($phone_number);
        $code = $this->generate_random_code($code_length);

        $data = [
            'code' => $code,
            'phone' => $phone_number,
            'expires' => time() + $expiry_seconds,
            'form_id' => $form_id,
            'countdown_text' => $countdown_text,
            'countdown_position' => $countdown_position,
            'lock_phone_field' => $lock_phone_field
        ];
        
        set_transient($transient_key, $data, $expiry_seconds);

        // Send SMS via pattern if configured
        if (!empty($token_data['sms_verification_sms_provider']) && !empty($token_data['sms_verification_pattern_code'])) {
            $sms_result = $this->send_sms_via_pattern($phone_number, $code, $token_data);
            
            if ($sms_result !== true) {
                wp_send_json_error([
                    'message' => __('خطا در ارسال پیامک: ', PAYAMENTOR_IRAN_DOMAIN) . $sms_result,
                    'type' => 'sms_error',
                    'data' => $response_data
                ], 500);
            }
        }

        // Send success response
        wp_send_json_success([
            'message' => __('کد تایید با موفقیت ارسال شد.', PAYAMENTOR_IRAN_DOMAIN),
            'expiry_time' => $expiry_seconds,
            'countdown_text' => $countdown_text,
            'countdown_position' => $countdown_position,
            'lock_phone_field' => $lock_phone_field,
            'phone_number' => $phone_number
        ]);
    }

    private function send_sms_via_pattern($phone_number, $verification_code, $settings) {
        require_once PAYAMENTOR_IRAN_DIR . 'includes/sms-gateways/registery/class-payamentor-smsgateway-registry.php';
        $registry = SMSGatewayRegistry::getInstance();
        
        // Prepare configuration based on provider
        $config = [];
        switch ($settings['sms_verification_sms_provider']) {
            case 'kavenegar-com':
            case 'sms-ir':
            case 'msgway-com':
                $config['apikey'] = $settings['sms_verification_api_key'];
                break;
            default:
                $config['username'] = $settings['sms_verification_username'];
                $config['password'] = $settings['sms_verification_password'];
                $config['from'] = $settings['sms_verification_sender_number'];
        }
    
        try {
            $smsGateway = $registry->getGateway($settings['sms_verification_sms_provider'], $config);
            
            if ($settings['sms_verification_sms_provider'] === 'Sunwaysms-com') {
                $message = str_replace(
                    'payamentor_vcode', 
                    $verification_code, 
                    $settings['sms_verification_pattern_text']
                );
                $result = $smsGateway->sendSMS($phone_number, $message);
            }
            elseif (method_exists($smsGateway, 'sendPatternSms')) {
                $pattern_vars = $this->parse_pattern_text($settings['sms_verification_pattern_text']);
                $parameters = [];
                foreach ($pattern_vars as $key => $value) {
                    if ($value === 'payamentor_vcode') {
                        $parameters[$key] = $verification_code;
                    }
                }
                $result = $smsGateway->sendPatternSms(
                    $phone_number,
                    $settings['sms_verification_pattern_code'],
                    $parameters
                );
                
                if ($result === true) {
                    error_log('پیامک با موفقیت ارسال شد.');
                    return true;
                } else {
                    error_log('خطا در ارسال پیامک: ' . print_r($result, true));
                    return $result;
                }
            } else {
                $error = 'سامانه پیامکی انتخاب‌ شده از ارسال پیامک با الگو پشتیبانی نمی‌کند.';
                return $error;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function parse_pattern_text($pattern_text) {
        $variables = [];
        $lines = explode("\n", $pattern_text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $variables[$key] = $value;
            }
        }
        
        return $variables;
    }

    public function add_countdown_script() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            window.smsVerificationSettings = {
                ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo wp_create_nonce('payamentor_nonce'); ?>',
                sending_text: '<?php echo esc_js(__('در حال ارسال...', 'payamentor-iran')); ?>',
                success_text: '<?php echo esc_js(__('کد تایید با موفقیت ارسال شد.', 'payamentor-iran')); ?>',
                error_text: '<?php echo esc_js(__('خطا در ارسال کد تایید', 'payamentor-iran')); ?>'
            };
        });
        </script>
        <?php
    }

    private function generate_random_code($length) {
        $characters = '0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    private function validate_iranian_phone($phone) {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        // الگوهای معتبر:
        // 09xxxxxxxxx // 989xxxxxxxxx // +989xxxxxxxxx // 00989xxxxxxxxx
        return preg_match('/^(?:0|98|\+98|0098)?9[0-9]{9}$/', $cleaned);
    }

    public function update_controls($widget): void {
        $elementor = Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
        
        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'button_text' => [
                'name' => 'button_text',
                'label' => __('متن دکمه', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'default' => __('ارسال کد تایید', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'phone_field_id' => [
                'name' => 'phone_field_id',
                'label' => __('آیدی فیلد شماره موبایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'description' => __('آیدی فیلد شماره موبایل را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content', 'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'empty_phone_message' => [
                'name' => 'empty_phone_message',
                'label' => __('پیغام شماره موبایل خالی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'default' => __('لطفاً شماره موبایل را وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content',  'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'validate_phone' => [
                'name' => 'validate_phone',
                'label' => __('اعتبارسنجی شماره موبایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('فعال', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => __('غیرفعال', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes',
                'default' => 'yes',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'invalid_phone_message' => [
                'name' => 'invalid_phone_message',
                'label' => __('پیغام شماره موبایل نامعتبر', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'default' => __('شماره موبایل معتبر نیست. لطفاً شماره را به صورت 09123456789 وارد کنید.', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content', 'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'validate_phone' => 'yes'
                ],
            ],
            'code_length' => [
                'name' => 'code_length',
                'label' => __('تعداد ارقام کد', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::NUMBER,
                'default' => $this->default_code_length,
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'expiry_time' => [
                'name' => 'expiry_time',
                'label' => __('زمان انقضای کد (ثانیه)', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::NUMBER,
                'default' => $this->default_expiry_time,
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'countdown_text' => [
                'name' => 'countdown_text',
                'label' => __('متن شمارش معکوس', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'default' => __('ثانیه تا انقضای کد', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content',  'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'countdown_position' => [
                'name' => 'countdown_position',
                'label' => __('موقعیت متن شمارش معکوس', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::SELECT,
                'default' => 'after',
                'options' => [
                    'before' => __('قبل از عدد', PAYAMENTOR_IRAN_DOMAIN),
                    'after' => __('بعد از عدد', 'payamentor-iran')
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'lock_phone_field' => [
                'name' => 'lock_phone_field',
                'label' => __('قفل کردن فیلد شماره موبایل', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('فعال', PAYAMENTOR_IRAN_DOMAIN),
                'label_off' => __('غیرفعال', PAYAMENTOR_IRAN_DOMAIN),
                'return_value' => 'yes', 'default' => 'no',
                'description' => __('با فعال کردن این گزینه، کاربر نمی‌تواند شماره موبایل را تا زمان انقضای کد تغییر دهد.', PAYAMENTOR_IRAN_DOMAIN),
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'sms_verification_sms_provider' => [
                'name' => 'sms_verification_sms_provider',
                'label' => __('سامانه پیامکی', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::SELECT2, 'label_block' => true, 'multiple' => false,
                'options' => [
                    '' => __('انتخاب کنید', PAYAMENTOR_IRAN_DOMAIN),
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
                'tab' => 'advanced',
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ], 
            'sms_verification_api_key' => [
                'name' => 'sms_verification_api_key',
                'label' => __('کلید API', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'tab' => 'advanced', 'label_block' => true,
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'sms_verification_sms_provider' => ['kavenegar-com', 'sms-ir', 'msgway-com']
                ],
            ],
            'sms_verification_username' => [
                'name' => 'sms_verification_username',
                'label' => __('نام کاربری', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'tab' => 'advanced', 'label_block' => true,
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'sms_verification_sms_provider' => ['farazsms-com', 'ippanel-com', 'Melipayamak-com', 'Asanak-com', 'Sunwaysms-com', 'Farapayamak.IR', 'modirpayamak-com', 'panelsmspro-ir',  'maxsms-co', 'tabansms-com']
                ],
            ],
            'sms_verification_password' => [
                'name' => 'sms_verification_password',
                'label' => __('رمز عبور', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'tab' => 'advanced', 'label_block' => true,
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'sms_verification_sms_provider' => ['farazsms-com', 'ippanel-com', 'Melipayamak-com', 'Asanak-com', 'Sunwaysms-com', 'Farapayamak.IR', 'modirpayamak-com', 'panelsmspro-ir',  'maxsms-co', 'tabansms-com']
                ],
            ],
            'sms_verification_sender_number' => [
                'name' => 'sms_verification_sender_number',
                'label' => __('شماره فرستنده', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'tab' => 'advanced', 'label_block' => true,
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'sms_verification_sms_provider!' => ['Sunwaysms-com', 'kavenegar-com', 'sms-ir', 'Melipayamak-com', 'Farapayamak.IR', 'msgway-com']
                ],
            ],
            'sms_verification_pattern_code' => [
                'name' => 'sms_verification_pattern_code',
                'label' => __('کد الگو', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXT,
                'tab' => 'advanced', 'label_block' => true,
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'sms_verification_sms_provider!' => ['Sunwaysms-com']
                ],
            ],
            'sms_verification_pattern_text' => [
                'name' => 'sms_verification_pattern_text',
                'label' => __('متن الگو', PAYAMENTOR_IRAN_DOMAIN),
                'type' => Controls_Manager::TEXTAREA,
                'default' => "code:payamentor_vcode",
                'tab' => 'advanced',
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
            ]
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    public function editor_preview_footer(): void {
        add_action('wp_footer', [$this, 'content_template_script']);
    }

    public function content_template_script(): void {
        ?>
        <script>
        jQuery(document).ready(() => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                function (inputField, item, i) {
                    const buttonText = item['button_text'] || '<?php echo esc_js(__('ارسال کد تایید', 'payamentor-iran')); ?>';
                    const emptyPhoneMessage = item['empty_phone_message'] || '<?php echo esc_js(__('لطفاً شماره موبایل را وارد کنید.', 'payamentor-iran')); ?>';
                    const invalidPhoneMessage = item['invalid_phone_message'] || '<?php echo esc_js(__('شماره موبایل معتبر نیست. لطفاً شماره را به صورت 09123456789 وارد کنید.', 'payamentor-iran')); ?>';
                    
                    return `<input class="elementor-field-textual sms-verification-input" 
                                type="button" 
                                value="${buttonText}" 
                                data-empty-phone-message="${emptyPhoneMessage}"
                                data-invalid-phone-message="${invalidPhoneMessage}"
                                readonly 
                                onfocus="this.blur();" 
                                style="cursor: pointer;" />
                            <div class="sms-verification-message"></div>`;
                }, 10, 3
            );
        });
        </script>
        <?php
    }
}