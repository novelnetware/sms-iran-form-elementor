<?php
namespace PayamentorIran\SMS;
if ( ! defined( 'ABSPATH' ) ) exit;

class Payamentor_SmsIR {

    private $apiKey;
    private $lineNumber;

    public function __construct($config) {
        $this->apiKey = $config['apikey'] ?? '';
        $this->lineNumber = $config['from'] ?? '';
    }


    public function sendSms($to, $message) {
        if (is_array($to)) {
            // ارسال گروهی
            return $this->sendBulkSms($to, $message);
        } else {
            // ارسال به یک شماره
            return $this->sendSingleSms($to, $message);
        }
    }

    private function sendSingleSms($to, $message) {
        $url = 'https://api.sms.ir/v1/send/bulk';
        $data = [
            "lineNumber" => $this->lineNumber,
            "messageText" => $message,
            "mobiles" => [$to],
            "sendDateTime" => null
        ];

        return $this->sendRequest($url, $data);
    }

    private function sendBulkSms($to, $message) {
        $url = 'https://api.sms.ir/v1/send/bulk';
        $data = [
            "lineNumber" => $this->lineNumber,
            "messageText" => $message,
            "mobiles" => $to,
            "sendDateTime" => null
        ];

        return $this->sendRequest($url, $data);
    }

    public function sendPatternSms($to, $templateId, $parameters) {
        $url = 'https://api.sms.ir/v1/send/verify';
        $data = [
            "mobile" => $to,
            "templateId" => $templateId,
            "parameters" => $this->prepareParameters($parameters)
        ];

        return $this->sendRequest($url, $data);
    }

    private function prepareParameters($parameters) {
        $formattedParameters = [];
        foreach ($parameters as $key => $value) {
            $formattedParameters[] = [
                "name" => $key,
                "value" => $value
            ];
        }
        return $formattedParameters;
    }

    private function sendRequest($url, $data) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $this->apiKey,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $this->checkedResult($response);
    }

    private function checkedResult($response) {
        $result = json_decode($response, true);
        // error_log('خطا در دریافت سامانه پیامکی: ' . print_r($result,true));
        if (isset($result['status']) && $result['status'] == 1) {
            return true; // ارسال موفق
        } else {
            $errorCode = $result['status'] ?? 'unknown';
            return $this->getErrorMessage($errorCode);
        }
    }

    private function getErrorMessage($errorCode) {
        $errorMessages = [
            0 => 'مشکلی در سامانه رخ داده است، لطفا با پشتیبانی در تماس باشید.',
            10 => 'کلید وب‌سرویس نامعتبر است.',
            11 => 'کلید وب‌سرویس غیرفعال است.',
            12 => 'کلید وب‌سرویس محدود به IP‌های تعریف شده می‌باشد.',
            13 => 'حساب کاربری غیرفعال است.',
            14 => 'حساب کاربری در حالت تعلیق قرار دارد.',
            20 => 'تعداد درخواست بیشتر از حد مجاز است.',
            101 => 'شماره خط نامعتبر می‌باشد.',
            102 => 'اعتبار کافی نمی‌باشد.',
            103 => 'درخواست شما دارای متن(های) خالی است.',
            104 => 'درخواست شما دارای موبایل(های) نادرست است.',
            105 => 'تعداد موبایل‌ها بیشتر از حد مجاز (100 عدد) می‌باشد.',
            106 => 'تعداد متن‌ها بیشتر از حد مجاز (100 عدد) می‌باشد.',
            107 => 'لیست موبایل‌ها خالی می‌باشد.',
            108 => 'لیست متن‌ها خالی می‌باشد.',
            109 => 'زمان ارسال نامعتبر می‌باشد.',
            110 => 'تعداد شماره موبایل‌ها و تعداد متن‌ها برابر نیستند.',
            111 => 'با این شناسه ارسالی ثبت نشده است.',
            112 => 'رکوردی برای حذف یافت نشد.',
            113 => 'قالب یافت نشد.',
            114 => 'طول رشته مقدار پارامتر، بیش از حد مجاز (25 کاراکتر) می‌باشد.',
            115 => 'شماره موبایل(ها) در لیست سیاه سامانه می‌باشند.',
            116 => 'نام پارامتر نمی‌تواند خالی باشد.',
            117 => 'متن ارسال شده مورد تایید نمی‌باشد.',
            118 => 'تعداد پیام‌ها بیش از حد مجاز می‌باشد.',
            119 => 'به منظور استفاده از قالب‌ شخصی‌سازی شده پلن خود را ارتقا دهید.',
            123 => 'خط ارسال‌کننده نیاز به فعال‌سازی دارد.'
        ];

        return $errorMessages[$errorCode] ?? 'خطای ناشناخته در ارسال پیامک.';
    }
}