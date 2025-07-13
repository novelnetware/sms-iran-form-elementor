<?php
namespace PayamentorIran\SMS;
if (!defined('ABSPATH')) exit;

class Payamentor_MSGWay {

    private $apiKey;
    
    public function __construct($config) {
        $this->apiKey = $config['apikey'] ?? '';
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException("API Key الزامی است.");
        }
    }
    
    public function sendPatternSms($to, $templateID, array $input_data) {
        try {
            $params = [
                "mobile" => is_array($to) ? implode(',', $to) : $to,
                "method" => "sms",
                "templateID" => (int) $templateID,
                "params" => $this->prepareUserPatternParameters($input_data)
            ];

            $response = $this->sendRequest('https://api.msgway.com/send', $params);
            return $this->checkResult($response);
        } catch (\Exception $ex) {
            error_log("خطا در ارسال پیامک با الگو: " . $ex->getMessage());
            return $ex->getMessage();
        }
    }
    
    private function prepareUserPatternParameters(array $input_data): array {
        $parameters = [];
        foreach ($input_data as $key => $value) {
            $parameters[] = $value; // تبدیل به آرایه‌ی ساده
        }
        return $parameters;
    }
    
    private function sendRequest($url, $params) {
        $headers = [
            'apiKey: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
    
    private function checkResult($response) {
        if (isset($response['status']) && $response['status'] === 'success') {
            return true;
        } else {
            $errorCode = $response['error']['code'] ?? 'unknown';
            $errorMessage = $response['error']['message'] ?? 'خطای ناشناخته';
            return "خطا: $errorCode - $errorMessage";
        }
    }
}