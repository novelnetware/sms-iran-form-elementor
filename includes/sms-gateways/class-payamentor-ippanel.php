<?php
namespace PayamentorIran\SMS;
if (!defined('ABSPATH')) exit;

class Payamentor_IPPanel {
    
    private $client;
    private $username;
    private $password;
    private $from;

    public function __construct($config, $password = '', $from = '') {
        if (is_array($config)) {
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
            $from = $config['from'] ?? '';
        } else {
            $username = $config;
        }

        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException("نام کاربری و رمز عبور الزامی هستند.");
        }

        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $this->client = new \SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        } catch (\SoapFault $e) {
            error_log("خطا در اتصال به سرور ippanel: " . $e->getMessage());
            throw new \Exception("خطا در اتصال به سرور ippanel: " . $e->getMessage());
        }

        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
    }


    public function sendSMS($toNum, $messageContent, $time = '') {
        try {
            $result = $this->client->SendSMS($this->from, $toNum, $messageContent, $this->username, $this->password, $time, 'send');
            $decodedResult = json_decode($result, true);
            if (is_array($decodedResult) && isset($decodedResult[0])) {
                return $this->checkResult($decodedResult[0]);
            } else {
                return "فرمت پاسخ سرور نامعتبر است.";
            }
        } catch (\SoapFault $ex) {
            return $ex->faultstring;
        }
    }

    public function sendPatternSms($toNum, $pattern_code, $input_data) {
        try {
            $result = $this->client->sendPatternSms($this->from, $toNum, $this->username, $this->password, $pattern_code, $input_data);
            
            $resultValue = json_decode($result, true);
            $resultValue = filter_var($resultValue, FILTER_VALIDATE_INT);
            
            if (isset($resultValue)) {
                if (is_numeric($resultValue) && strlen((string)$resultValue) >= 10) {
                    return true;
                }
                elseif (is_numeric($resultValue)) {
                    return $this->getErrorMessage($resultValue);
                }
                else {
                    return $resultValue;
                }
            }
            // return "فرمت پاسخ سرور نامعتبر است: " . print_r($result, true);
            
        } catch (\SoapFault $ex) {
            error_log("خطا در ارسال پیامک با الگو: " . $ex->getMessage());
            return $ex->faultstring;
        }
    }

    // private function checkResult($result) {
    //     if (is_numeric($result) && strlen($result) >= 10) {
    //         return true;
    //     }
    //     elseif (is_numeric($result) && strlen($result) < 10) {
    //         return $this->getErrorMessage($result);
    //     }
    //     else {
    //         return $result; // همان متن خطا را بازگردان
    //     }
    // }

    private function getErrorMessage($errorCode) {
        $errorCodes = [
            // 0 => 'عملیات با موفقیت انجام شده است.',
            1 => 'متن پیام خالی می‌باشد.',
            2 => 'کاربر محدود گردیده است.',
            3 => 'خط به شما تعلق ندارد.',
            4 => 'گیرندگان خالی است.',
            5 => 'اعتبار کافی نیست.',
            7 => 'خط مورد نظر برای ارسال انبوه مناسب نمی‌باشد.',
            9 => 'خط مورد نظر در این ساعت امکان ارسال ندارد.',
            21 => 'پسوند فایل صوتی نامعتبر است.',
            22 => 'سایز فایل صوتی نامعتبر است.',
            23 => 'تعداد تلاش در پیام صوتی نامعتبر است.',
            98 => 'حداکثر تعداد گیرنده رعایت نشده است.',
            99 => 'اپراتور خط ارسالی قطع می‌باشد.',
            100 => 'شماره مخاطب دفترچه تلفن نامعتبر می‌باشد.',
            101 => 'شماره مخاطب در دفترچه تلفن وجود دارد.',
            102 => 'شماره مخاطب با موفقیت در دفترچه تلفن ذخیره گردید.',
            111 => 'حداکثر تعداد گیرنده برای ارسال پیام صوتی رعایت نشده است.',
            131 => 'تعداد تلاش در پیام صوتی باید یکبار باشد.',
            132 => 'آدرس فایل صوتی وارد نگردیده است.',
            266 => 'ارسال با خط اشتراکی امکان پذیر نیست.',
            301 => 'از حرف ویژه در نام کاربری استفاده گردیده است.',
            302 => 'قیمت گذاری انجام نگریده است.',
            303 => 'نام کاربری وارد نگردیده است.',
            304 => 'نام کاربری قبال انتخاب گردیده است.',
            305 => 'نام کاربری وارد نگردیده است.',
            306 => 'کد ملی وارد نگردیده است.',
            307 => 'کد ملی به خطا وارد شده است.',
            308 => 'شماره شناسنامه نامعتبر است.',
            309 => 'شماره شناسنامه وارد نگردیده است.',
            310 => 'ایمیل کاربر وارد نگردیده است.',
            311 => 'شماره تلفن وارد نگردیده است.',
            312 => 'تلفن به درستی وارد نگردیده است.',
            313 => 'آدرس شما وارد نگردیده است.',
            314 => 'شماره موبایل را وارد نکرده‌اید.',
            315 => 'شماره موبایل به نادرستی وارد گردیده است.',
            316 => 'سطح دسترسی به نادرستی وارد گردیده است.',
            317 => 'کلمه عبور وارد نگردیده است.',
            404 => 'پترن در دسترس نیست.',
            455 => 'ارسال در آینده برای کد بالک ارسالی لغو شد.',
            456 => 'کد بالک ارسالی نامعتبر است.',
            458 => 'کد تیکت نامعتبر است.',
            962 => 'نام کاربری یا کلمه عبور نادرست می‌باشد.',
            963 => 'دسترسی نامعتبر می‌باشد.',
            964 => 'شما دسترسی نمایندگی ندارید.',
            970 => 'پارامتر های ارسالی برای پترن نامعتبر است.',
            971 => 'پترن ارسالی نامعتبر است.',
            972 => 'دریافت کننده برای ارسال پترن نامعتبر می‌باشد.',
            992 => 'ارسال پیام از ساعت 8 تا 23 می‌باشد.',
            993 => 'دفترچه تلفن باید یک آرایه باشد.',
            994 => 'لطفاً تصویری از کارت بانکی خود را از منوی مدارک ارسال کنید.',
            995 => 'جهت ارسال با خطوط اشتراکی سامانه، لطفاً شماره کارت بانکی خود را به دلیل تکمیل فرایند احراز هویت از بخش ارسال مدارک ثبت نمایید.',
            996 => 'پترن فعال نیست.',
            997 => 'شما اجازه ارسال از این پترن را ندارید.',
            998 => 'کارت ملی یا کارت بانکی شما تایید نشده است.',
            1001 => 'فرمت نام کاربری درست نمی‌باشد. (حداقل ۵ کاراکتر، فقط حروف و اعداد)',
            1002 => 'گذرواژه خیلی ساده می‌باشد. باید حداقل 8 کاراکتر بوده و از نام کاربری و ایمیل و شماره موبایل خود در آن استفاده نکنید.',
            1003 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
            1004 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
            1005 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
            1006 => 'تاریخ ارسال پیام برای گذشته می‌باشد، لطفاً تاریخ ارسال پیام را به درستی وارد نمایید.',
        ];
    
        return $errorCodes[$errorCode] ?? "اشکال تعریف نشده با کد: $errorCode";
    }
    
}