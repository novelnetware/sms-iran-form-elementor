<?php
namespace PayamentorIran\BotGateways;

if (!defined('ABSPATH')) exit;

class BaleBot {
    private $token;
    private $apiUrl = 'https://tapi.bale.ai/bot';

    public function __construct($token) {
        $this->token = $token;
    }

    private function sendRequest($method, $data) {
        $url = $this->apiUrl . $this->token . '/' . $method;
        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$result, $httpCode];
    }

    public function sendMessage($chatId, $text, $replyToMessageId = null, $replyMarkup = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_to_message_id' => $replyToMessageId,
            'reply_markup' => $replyMarkup
        ];
        list($result, $httpCode) = $this->sendRequest('sendMessage', $data);
        
        // error_log("BaleBot Response: " . print_r($result, true));
    
        return $httpCode == 200 ? json_decode($result, true) : null;
    }

    public function sendMediaGroup($chatId, $media, $replyToMessageId = null) {
        $data = [
            'chat_id' => $chatId,
            'media' => $media,
            'reply_to_message_id' => $replyToMessageId
        ];
        list($result, $httpCode) = $this->sendRequest('sendMediaGroup', $data);
        return $httpCode == 200 ? json_decode($result, true) : null;
    }

    public function sendPhoto($chatId, $photo, $caption = '', $replyToMessageId = null) {
        $data = [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'reply_to_message_id' => $replyToMessageId
        ];
        list($result, $httpCode) = $this->sendRequest('sendPhoto', $data);
        return $httpCode == 200 ? json_decode($result, true) : null;
    }

    public function sendAudio($chatId, $audio, $caption = '', $replyToMessageId = null) {
        $data = [
            'chat_id' => $chatId,
            'audio' => $audio,
            'caption' => $caption,
            'reply_to_message_id' => $replyToMessageId
        ];
        list($result, $httpCode) = $this->sendRequest('sendAudio', $data);
        return $httpCode == 200 ? json_decode($result, true) : null;
    }

    public function sendDocument($chatId, $document, $caption = '', $replyToMessageId = null) {
        $data = [
            'chat_id' => $chatId,
            'document' => $document,
            'caption' => $caption,
            'reply_to_message_id' => $replyToMessageId
        ];
        list($result, $httpCode) = $this->sendRequest('sendDocument', $data);
        return $httpCode == 200 ? json_decode($result, true) : null;
    }

    public function sendVideo($chatId, $video, $caption = '', $replyToMessageId = null) {
        $data = [
            'chat_id' => $chatId,
            'video' => $video,
            'caption' => $caption,
            'reply_to_message_id' => $replyToMessageId
        ];
        list($result, $httpCode) = $this->sendRequest('sendVideo', $data);
        return $httpCode == 200 ? json_decode($result, true) : null;
    }
}

?>
