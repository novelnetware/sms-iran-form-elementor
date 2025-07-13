<?php
namespace PayamentorIran\SMS;
if (!defined('ABSPATH')) exit;

class Payamentor_Asanak {

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
    }

    public function sendSMS($toNum, $messageContent, $time = '') {
        try {
            $destination = is_array($toNum) ? implode(',', $toNum) : $toNum;
            $response = $this->callApi('sendsms', [
                'Source' => $this->from,
                'Message' => $messageContent,
                'destination' => $destination
            ]);
            return $this->checkResult($response['meta']['status']);
        } catch (\Exception $ex) {
            error_log("خطا در ارسال پیامک: " . $ex->getMessage());
            return $ex->getMessage();
        }
    }

    public function sendPatternSms($toNum, $pattern_code, $input_data) {
        try {
            $destination = is_array($toNum) ? implode(',', $toNum) : $toNum;
            $parameters = $this->prepareUserPatternParameters($input_data);
            $response = $this->callApi('template', [
                'template_id' => $pattern_code,
                'destination' => $destination,
                'parameters' => $parameters
            ], true);
            return $this->checkResult($response['meta']['status']);
        } catch (\Exception $ex) {
            // error_log("خطا در ارسال پیامک با الگو: " . $ex->getMessage());
            return $ex->getMessage();
        }
    }

    private function prepareUserPatternParameters(array $input_data): array {
        $parameters = [];
        foreach ($input_data as $key => $value) {
            $parameters[$key] = $value; // تبدیل به فرمت مورد نیاز
        }
        return $parameters;
    }

    private function callApi($endpoint, $data, $isJson = false) {
        $url = "https://panel.asanak.com/webservice/v2rest/$endpoint";
        $headers = $isJson ? ["Accept: application/json", "Content-Type: application/json"] : [];
        $postFields = $isJson ? json_encode(array_merge(['username' => $this->username, 'password' => $this->password], $data)) : array_merge(['username' => $this->username, 'password' => $this->password], $data);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function checkResult($result) {
        return $result == 200 ? true : $this->getErrorMessage($result);
    }

    private function getErrorMessage($errorCode) {
        $errorCodes = [
            200 => 'عملیات موفق.',
            400 => 'درخواست نامعتبر.',
            401 => 'احراز هویت ناموفق.',
            403 => 'دسترسی ممنوع.',
            404 => 'منبع یافت نشد.',
            500 => 'خطای سرور.',
            503 => 'سرور موقتاً در دسترس نیست.',
        ];
        return $errorCodes[$errorCode] ?? "خطای ناشناخته: $errorCode";
    }
}