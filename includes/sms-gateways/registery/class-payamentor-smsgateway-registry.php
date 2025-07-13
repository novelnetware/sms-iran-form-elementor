<?php
namespace PayamentorIran\SMS\Registery;

if (!defined('ABSPATH')) exit;

class SMSGatewayRegistry {

    private static $instance = null;
    private $gateways = [];
    
    // نگاشت نام سامانه‌ها به نام کلاس‌ها
    private $gatewayMapping = [
        'ippanel-com'      => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'farazsms-com'     => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'modirpayamak-com' => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'maxsms-co'        => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'tabansms-com'     => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'panelsmspro-ir'   => ['class' => 'IPPanel', 'file' => 'ippanel'],
        'Melipayamak-com'  => ['class' => 'Melipayamak', 'file' => 'melipayamak'],
        'Farapayamak.IR'   => ['class' => 'Melipayamak', 'file' => 'melipayamak'],
        'kavenegar-com'    => ['class' => 'Kavenegar', 'file' => 'kavenegar'],
        'sms-ir'           => ['class' => 'SmsIR', 'file' => 'smsir'],
        'Asanak-com'       => ['class' => 'Asanak', 'file' => 'asanak'],
        'Sunwaysms-com'    => ['class' => 'SunWaySms', 'file' => 'sunwaysms'],
        'msgway-com'    => ['class' => 'MSGWay', 'file' => 'msgway'],
    ];
    private function __construct() {
        // جلوگیری از ایجاد نمونه‌های جدید خارج از کلاس
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getGateway($gatewayName, $config = []) {
        if (!isset($this->gateways[$gatewayName])) {
            $this->loadGateway($gatewayName, $config);
        }
        return $this->gateways[$gatewayName];
    }

    private function loadGateway($gatewayName, $config) {
        if (!isset($this->gatewayMapping[$gatewayName])) {
            throw new \Exception("سامانه پیامکی {$gatewayName} پشتیبانی نمی‌شود.");
        }
    
        // دریافت نام کلاس و فایل از نگاشت
        $gatewayInfo = $this->gatewayMapping[$gatewayName];
        $className = "PayamentorIran\\SMS\\Payamentor_" . $gatewayInfo['class'];
        $file = PAYAMENTOR_IRAN_DIR . "includes/sms-gateways/class-payamentor-" . $gatewayInfo['file'] . ".php";
    
        if (file_exists($file)) {
            require_once $file;
            if (class_exists($className)) {
                // ایجاد نمونه از کلاس
                $this->gateways[$gatewayName] = new $className($config);
            } else {
                throw new \Exception("کلاس {$className} وجود ندارد.");
            }
        } else {
            throw new \Exception("فایل {$file} وجود ندارد.");
        }
    }
    
}