<?php
namespace PayamentorIran\SMS;
if ( ! defined( 'ABSPATH' ) ) exit;

class Payamentor_Melipayamak {

    private $username;
    private $password;
    private $from;
    private $soapClient;

    public function __construct($config) {
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->from = $config['from'] ?? '';
        ini_set("soap.wsdl_cache_enabled", "0");
        $this->soapClient = new \SoapClient("http://api.payamak-panel.com/post/Send.asmx?wsdl", array("encoding" => "UTF-8"));
    }

    public function sendSms($to, $message, $isFlash = false) {
        $data = [
            "username" => $this->username,
            "password" => $this->password,
            "to" => $to,
            "from" => $this->from,
            "text" => $message,
            "isflash" => $isFlash
        ];

        $result = $this->soapClient->SendSimpleSMS2($data)->SendSimpleSMS2Result;
        return $this->checkedResult($result);
    }

    public function sendPatternSms($to, $bodyId, $parameters) {
        $text = implode(';', array_values($parameters));
    
        $data = [
            "username" => $this->username,
            "password" => $this->password,
            "to" => $to,
            "text" => $text,
            "bodyId" => $bodyId
        ];
    
        $result = $this->soapClient->SendByBaseNumber2($data)->SendByBaseNumber2Result;
        return $this->checkedResult($result);
    }

    private function checkedResult($result) {
        if (is_numeric($result) && strlen($result) > 10) {
            return true; // ارسال موفق
        } else {
            switch ($result) {
                case '0':
                    return 'نام کاربری یا رمز عبور اشتباه است.';
                case '2':
                    return 'اعتبار کافی نیست.';
                case '3':
                    return 'محدودیت در ارسال روزانه.';
                case '4':
                    return 'محدودیت در حجم ارسال.';
                case '5':
                    return 'شماره فرستنده معتبر نیست.';
                case '6':
                    return 'سامانه در حال بروزرسانی است.';
                case '7':
                    return 'متن حاوی کلمه فیلتر شده است.';
                case '9':
                    return 'ارسال از خطوط عمومی از طریق وب‌سرویس امکان‌پذیر نیست.';
                case '10':
                    return 'کاربر مورد نظر فعال نیست.';
                case '11':
                    return 'پیامک ارسال نشد.';
                case '12':
                    return 'مدارک کاربر کامل نیست.';
                case '14':
                    return 'متن حاوی لینک است.';
                case '15':
                    return 'ارسال به بیش از یک شماره همراه بدون درج "لغو11" ممکن نیست.';
                case '16':
                    return 'شماره گیرنده یافت نشد.';
                case '17':
                    return 'متن پیامک خالی است.';
                case '35':
                    return 'شماره گیرنده در لیست سیاه مخابرات است.';
                default:
                    return 'خطای ناشناخته در ارسال پیامک.';
            }
        }
    }
}