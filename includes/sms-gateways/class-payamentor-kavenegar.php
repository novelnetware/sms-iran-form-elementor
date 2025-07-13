<?php
namespace PayamentorIran\SMS;
if (!defined('ABSPATH')) exit;

class Payamentor_Kavenegar {
    private $api_key;

    public function __construct($config) {
        if (is_array($config)) {
            $this->api_key = $config['apikey'];
        } else {
            $this->api_key = $config;
        }

    }

    public function sendSms($to, $message) {
        $url = 'https://api.kavenegar.com/v1/' . $this->api_key . '/sms/send.json';
        $data = [
            'receptor' => is_array($to) ? implode(',', $to) : $to,
            'message' => $message
        ];
        $result = json_decode($this->sendRequest($url, 'POST', $data), true);
        return $this->checkedResult($result['return']['status']);
    }

    public function sendPatternSms($to, $template, array $parameters) {
        if (is_array($to)) {
            $to = implode(',', $to);
        }
        $params = [
            "receptor" => $to,
            "template" => (string)$template
        ];

        if (isset($parameters['1'])) {
            $params['token'] = (string)$parameters['1'];
        }
        if (isset($parameters['2'])) {
            $params['token2'] = (string)$parameters['2'];
        }
        if (isset($parameters['3'])) {
            $params['token3'] = (string)$parameters['3'];
        }
        if (isset($parameters['10'])) {
            $params['token10'] = (string)$parameters['10'];
        }
        if (isset($parameters['20'])) {
            $params['token20'] = (string)$parameters['20'];
        }

        $url = 'https://api.kavenegar.com/v1/' . $this->api_key . '/verify/lookup.json';
        // error_log('خطا در دریافت params: ' . print_r($params,true));

        $result = json_decode($this->sendRequest($url, 'POST', $params), true);
        // error_log('خطا در دریافت result: ' . print_r($result,true));

        return $this->checkedResult($result['return']['status']);
    }

    private function sendRequest($url, $method, $data) {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'x-api-key: ' . $this->api_key
        ];

        $curl = curl_init();
        $fields_string = http_build_query($data);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function checkedResult($result) {
        if ($result == 200) {
            return true;
        } else {
            switch ($result) {
                case 400:
                    return 'پارامترها ناقص هستند';
                case 401:
                    return 'حساب کاربری غیرفعال شده است';
                case 402:
                    return 'عملیات ناموفق بود';
                case 403:
                    return 'کد شناسائی API-Key معتبر نمی‌باشد';
                case 404:
                    return 'متد نامشخص است';
                case 405:
                    return 'متد Get/Post اشتباه است';
                case 406:
                    return 'پارامترهای اجباری خالی ارسال شده اند';
                case 407:
                    return 'دسترسی به اطلاعات مورد نظر برای شما امکان پذیر نیست';
                case 408:
                    return 'برای استفاده از متدهای Select، SelectOutbox و LatestOutBox و یا ارسال با خط بین المللی نیاز به تنظیم IP در بخش تنظیمات امنیتی می باشد';
                case 409:
                    return 'سرور قادر به پاسخگوئی نیست بعدا تلاش کنید';
                case 411:
                    return 'دریافت کننده نامعتبر است';
                case 412:
                    return 'ارسال کننده نامعتبر است';
                case 413:
                    return 'پیام خالی است و یا طول پیام بیش از حد مجاز می‌باشد. حداکثر طول کل متن پیامک 900 کاراکتر می باشد';
                case 414:
                    return 'حجم درخواست بیشتر از حد مجاز است ،ارسال پیامک :هر فراخوانی حداکثر 200 رکورد و کنترل وضعیت :هر فراخوانی 500 رکورد';
                case 415:
                    return 'اندیس شروع بزرگ تر از کل تعداد شماره های مورد نظر است';
                case 416:
                    return 'IP سرویس مبدا با تنظیمات مطابقت ندارد';
                case 417:
                    return 'تاریخ ارسال اشتباه است و فرمت آن صحیح نمی باشد.';
                case 418:
                    return 'اعتبار شما کافی نمی‌باشد';
                case 419:
                    return 'طول آرایه متن و گیرنده و فرستنده هم اندازه نیست';
                case 420:
                    return 'استفاده از لینک در متن پیام برای شما محدود شده است';
                case 422:
                    return 'داده ها به دلیل وجود کاراکتر نامناسب قابل پردازش نیست';
                case 424:
                    return 'الگوی مورد نظر پیدا نشد';
                case 426:
                    return 'استفاده از این متد نیازمند سرویس پیشرفته می‌باشد';
                case 427:
                    return 'استفاده از این خط نیازمند ایجاد سطح دسترسی می باشد';
                case 428:
                    return 'ارسال کد از طریق تماس تلفنی امکان پذیر نیست';
                case 429:
                    return 'IP محدود شده است';
                case 431:
                    return 'ساختار کد صحیح نمی‌باشد';
                case 432:
                    return 'پارامتر کد در متن پیام پیدا نشد';
                case 451:
                    return 'فراخوانی بیش از حد در بازه زمانی مشخص IP محدود شده';
                case 501:
                    return 'فقط امکان ارسال پیام تست به شماره صاحب حساب کاربری وجود دارد';
                default:
                    return 'پاسخ ناشناخته';
            }
        }
    }
}
?>
