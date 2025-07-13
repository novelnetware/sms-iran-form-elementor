<?php
namespace PayamentorIran\BotGateways;

if (!defined('ABSPATH')) exit;

class TelegramBot {
    private $token;
    private $apiUrl = 'https://api.telegram.org/bot';

    public function __construct($token) {
        $this->token = $token;
    }

    private function sendRequest($method, $args, $proxyUrl = null) {
        $url = $proxyUrl ? $proxyUrl : ($this->apiUrl . $this->token . '/' . $method);

        $data = $proxyUrl
            ? http_build_query([
                'bot_token' => $this->token,
                'method' => $method,
                'args' => json_encode($args),
            ])
            : http_build_query($args);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // تنظیم هدرها
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . ($proxyUrl ? 'application/x-www-form-urlencoded' : 'application/json')
        ]);

        // فعال کردن هدایت خودکار
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        curl_close($ch);
        return [$result, $httpCode];
    }

    // ارسال پیام
    public function sendMessage($chat_id, $text, $replyToMessageId = null, $replyMarkup = null, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendMessage', $chat_id, ['text' => $text], $replyToMessageId, $replyMarkup, $proxyUrl);
    }

    // ارسال تصاویر
    public function sendPhoto($chat_id, $photo, $caption = '', $replyToMessageId = null, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendPhoto', $chat_id, ['photo' => $photo, 'caption' => $caption], $replyToMessageId, null, $proxyUrl);
    }

    // ارسال ویدیو
    public function sendVideo($chat_id, $video, $caption = '', $replyToMessageId = null, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendVideo', $chat_id, ['video' => $video, 'caption' => $caption], $replyToMessageId, null, $proxyUrl);
    }

    // ارسال فایل صوتی
    public function sendAudio($chat_id, $audio, $caption = '', $replyToMessageId = null, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendAudio', $chat_id, ['audio' => $audio, 'caption' => $caption], $replyToMessageId, null, $proxyUrl);
    }

    // ارسال اسناد
    public function sendDocument($chat_id, $document, $caption = '', $replyToMessageId = null, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendDocument', $chat_id, ['document' => $document, 'caption' => $caption], $replyToMessageId, null, $proxyUrl);
    }

    // ارسال گروه رسانه‌ای (چندین رسانه)
    public function sendMediaGroup($chat_id, $media, $proxyUrl = null) {
        return $this->sendGenericMediaRequest('sendMediaGroup', $chat_id, ['media' => $media], null, null, $proxyUrl);
    }

    // متد عمومی برای ارسال رسانه‌های مختلف (تصویر، ویدیو، فایل، ...)
    private function sendGenericMediaRequest($method, $chat_id, $mediaArgs, $replyToMessageId = null, $replyMarkup = null, $proxyUrl = null) {
        // تنظیم پارامترهای مشترک
        $args = array_merge([
            'chat_id' => $chat_id,
            'reply_to_message_id' => $replyToMessageId,
            'reply_markup' => $replyMarkup,
        ], $mediaArgs);

        // ارسال درخواست به API تلگرام
        return $this->sendRequest($method, $args, $proxyUrl);
    }
}
?>
