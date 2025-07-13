<?php
namespace PayamentorIran\SMS;
if (!defined('ABSPATH')) exit;

class Payamentor_SunWaySms {

    private $client;
    private $username;
    private $password;
    private $from;

    public function __construct($config, $password = '', $from = '') {
        if (is_array($config)) {
            $this->username = $config['username'] ?? '';
            $this->password = $config['password'] ?? '';
            $this->from = $config['from'] ?? '';
        } else {
            $this->username = $config;
            $this->password = $password;
            $this->from = $from;
        }

        if (empty($this->username) || empty($this->password)) {
            throw new \InvalidArgumentException("نام کاربری و رمز عبور الزامی هستند.");
        }

        try {
            $this->client = new \SoapClient("https://sms.sunwaysms.com/SMSWS/SOAP.asmx?wsdl");
        } catch (\SoapFault $e) {
            error_log("خطا در اتصال به سرور SunWaySMS: " . $e->getMessage());
            throw new \Exception("خطا در اتصال به سرور SunWaySMS: " . $e->getMessage());
        }
    }
    
    public function sendSMS($toNum, $messageContent) {
        try {
            $recipientNumbers = is_array($toNum) ? $toNum : [$toNum];

            $result = $this->client->SendArray([
                'UserName' => $this->username,
                'Password' => $this->password,
                'RecipientNumber' => $recipientNumbers,
                'MessageBody' => $messageContent,
                'SpecialNumber' => $this->from,
                'IsFlashMessage' => false,
                'CheckingMessageID' => null
            ]);

            if (isset($result->SendArrayResult)) {
                $response = $result->SendArrayResult;
                if (is_array($response)) {
                    foreach ($response as $code) {
                        if ($code < 1000 && $code > 50) {
                            return $this->getErrorMessage($code); // خطا در ارسال
                        }
                    }
                    return true; // ارسال موفق
                }
            }
            return "خطا در پردازش پاسخ سرور.";
        } catch (\SoapFault $ex) {
            error_log("خطا در ارسال پیامک: " . $ex->getMessage());
            return $ex->getMessage();
        }
    }

    private function getErrorMessage($errorCode) {
        $errorCodes = [
            50 => 'عملیات موفقیت‌آمیز بود.',
            51 => 'نام کاربری یا رمز عبور اشتباه است.',
            52 => 'نام کاربری یا رمز عبور خالی است.',
            53 => 'طول کلید RecipientNumber بیش از حد مجاز است (بیش از 1000 عدد).',
            54 => 'کلید RecipientNumber خالی است.',
            55 => 'کلید RecipientNumber نامعتبر است (مقدار آن Null است).',
            56 => 'طول آرایه MessageID بیش از حد مجاز است (بیش از 1000 عدد).',
            57 => 'کلید MessageID خالی است.',
            58 => 'کلید MessageID نامعتبر است (مقدار آن Null است).',
            59 => 'کلید MessageBody خالی است.',
            60 => 'سرور به علت ترافیک بالا قادر به پاسخ‌گویی نیست.',
            61 => 'کلید SpecialNumber نامعتبر است (شماره اختصاصی وجود ندارد یا متعلق به این کاربر نیست).',
            62 => 'کلید SpecialNumber خالی است.',
            63 => 'این IP اجازه دسترسی به وب‌سرویس این کاربر را ندارد.',
            65 => 'کلید NumberOfMessage اشتباه است (مقدار آن منفی است).',
            66 => 'طول کلید CheckingMessageID با طول کلید RecipientNumber برابر نیست.',
            67 => 'طول آرایه CheckingMessageID بیش از حد مجاز است (بیش از 50 عدد).',
            68 => 'کلید CheckingMessageID خالی است.',
            69 => 'کلید CheckingMessageID نامعتبر است (مقدار آن Null است).',
            70 => 'کاربر غیرفعال شده است.',
            72 => 'ترکیب پارامترهای زمان اشتباه است.',
            73 => 'ترکیب پارامترهای تاریخ اشتباه است.',
            74 => 'طول کلید NumberGroupID بیش از حد مجاز است (بیش از 1000 عدد).',
            75 => 'کلید NumberGroupID خالی است.',
            76 => 'کلید NumberGroupID نامعتبر است (مقدار آن Null است).',
            77 => 'شما کاربر وب‌سرویس نیستید.',
            78 => 'شما کاربر سامانه مدیریت ارسال و دریافت پیام کوتاه نیستید.',
            79 => 'طول کلید PersonName با طول PersonNumber برابر نیست.',
            80 => 'وب‌سرویس توسط Admin غیرفعال شده است.',
            81 => 'طول کلید PersonNumber بیش از حد مجاز است (بیش از 1000 عدد).',
            82 => 'کلید PersonNumber خالی است.',
            83 => 'کلید PersonNumber نامعتبر است (مقدار آن Null است).',
            84 => 'شماره گروه دفتر تلفن (NumberGroupID) نامعتبر است.',
            201 => 'فرمت شماره RecipientNumber اشتباه است.',
            202 => 'اپراتور مخابراتی شماره RecipientNumber برای سیستم ناشناخته است.',
            203 => 'به علت کمبود اعتبار، امکان ارسال به این شماره وجود ندارد.',
            204 => 'هیچ شناسه‌ای با مقدار CheckingMessageID در سیستم وجود ندارد.',
            205 => 'فرمت شماره PersonNumber اشتباه است.',
            206 => 'شماره اپراتور نامعتبر است.',
            207 => 'عنوان انگلیسی گروه دفتر تلفن نامعتبر است.',
            300 => 'ارسال پیامک حاوی لینک مجاز نیست.',
            400 => 'تعداد درخواست‌های ارسالی از حد مجاز بیشتر است.',
            666 => 'سرویس موقتاً غیرفعال است.',
            777 => 'این IP مسدود است.',
            888 => 'برای شماره فرستنده احراز هویت ثبت نشده است.',
            999 => 'ارسال این پیامک مجاز نیست.',
        ];

        return $errorCodes[$errorCode] ?? "خطای ناشناخته با کد: $errorCode";
    }
}