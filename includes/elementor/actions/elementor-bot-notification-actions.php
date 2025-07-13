<?php
namespace PayamentorIran\Elementor\Actions;

use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

if (!defined('ABSPATH')) exit;

class Elementor_Bot_Notification_Actions {
    private $botConfigs = [
        'bale-ai' => [
            'class' => 'BaleBot',
            'token_key' => 'bot_iran_bale_ai_token',
            'user_id_key' => 'bot_iran_bale_ai_userid',
        ],
        'telegram' => [
            'class' => 'TelegramBot',
            'token_key' => 'bot_iran_telegram_token',
            'user_id_key' => 'bot_iran_telegram_userid',
        ],
    ];

    public function __construct() {
        add_action('elementor_pro/forms/process', [$this, 'handle_bot_notification'], 10, 2);
    }

    public function handle_bot_notification(Form_Record $record, Ajax_Handler $ajax_handler) {
        $settings = $record->get('form_settings');
        $botList = $settings['bot_iran_form_list'] ?? [];
        if (empty($botList)) return;

        $fields = array_map(fn($field) => $field['value'], $record->get('fields'));
        $message = $this->replaceFieldPlaceholders($settings['bot_iran_form_message'] ?? '', $fields);
        $enableMessage = $settings['bot_iran_form_enable_text_message'] === 'yes';
        $enableFiles = $settings['bot_iran_form_enable_file_message'] === 'yes';
        $proxyUrl = $settings['bot_iran_internal_host_switch'] === 'yes' ? ($settings['bot_iran_proxy_url'] ?? '') : null;

        foreach ($botList as $bot) {
            if (!isset($this->botConfigs[$bot])) continue;

            $config = $this->botConfigs[$bot];
            $token = $settings[$config['token_key']] ?? '';
            $userIds = preg_split('/[\*,]/', $settings[$config['user_id_key']] ?? '', -1, PREG_SPLIT_NO_EMPTY);

            if (empty($token) || empty($userIds)) continue;

            $botClass = "PayamentorIran\\BotGateways\\{$config['class']}";
            require_once PAYAMENTOR_IRAN_DIR . "/includes/bot-gateways/class-payamentor-{$bot}.php";
            $botInstance = new $botClass($token);

            foreach ($userIds as $userId) {
                $lastMessageId = null;

                if ($enableFiles) {
                    $lastMessageId = $this->sendFiles($botInstance, $userId, $settings, $fields, $proxyUrl);
                }

                if ($enableMessage && !empty($message)) {
                    $response = $botInstance->sendMessage($userId, $message, $lastMessageId, null, $proxyUrl);
                    $this->handleResponse($response, $settings, $ajax_handler, $config['class']);
                }
            }
        }
    }

    private function sendFiles($botInstance, $userId, $settings, $fields, $proxyUrl) {
        $fileFields = $settings['bot_iran_form_file_fields'] ?? [];
        $lastMessageId = null;

        foreach ($fileFields as $fileField) {
            $fileId = $fileField['file_id_fields'] ?? '';
            if (empty($fileId) || !isset($fields[$fileId])) {
                // error_log("File field '$fileId' is empty or not set.");
                continue;
            }

            $fileUrl = $fields[$fileId];
            $fileType = $fileField['file_type'] ?? '';
            $caption = $this->replaceFieldPlaceholders($fileField['file_caption'] ?? '', $fields);

            $method = null;
            switch ($fileType) {
                case 'image':
                    $method = 'sendPhoto';
                    break;
                case 'video':
                    $method = 'sendVideo';
                    break;
                case 'audio':
                    $method = 'sendAudio';
                    break;
                case 'document':
                    $method = 'sendDocument';
                    break;
                default:
                    $method = null;
            }

            if ($method) {
                $response = $botInstance->$method($userId, $fileUrl, $caption, $lastMessageId, $proxyUrl);
                if (is_array($response)) {
                    $result = $response[0] ? json_decode($response[0], true) : null;
                } else {
                    $result = $response;
                }

                if (isset($result['result']['message_id'])) {
                    $lastMessageId = $result['result']['message_id'];
                } elseif (isset($result['ok']) && !$result['ok']) {
                    // error_log("File send failed: " . $result['description'] ?? 'Unknown error');
                }
            }
        }
        return $lastMessageId;
    }

    private function replaceFieldPlaceholders($text, $fields) {
        $text = preg_replace_callback('/%([^%]+)%/', fn($matches) => $fields[$matches[1]] ?? '', $text);
        $text = preg_replace_callback('/\[field id="([^"]+)"\]/', fn($matches) => $fields[$matches[1]] ?? '', $text);
        return $text;
    }

    private function handleResponse($response, $settings, $ajax_handler, $botClass) {
        if ($settings['bot_iran_form_test_message'] !== 'yes') return;

        if (is_array($response)) {
            $result = $response[0] ? json_decode($response[0], true) : null;
        } else {
            $result = $response;
        }
        error_log("Message send response for $botClass: " . print_r($result, true));

        if (!$result || (isset($result['ok']) && !$result['ok'])) {
            $ajax_handler->add_admin_error_message(__('خطا در ارسال پیام', 'payamentor-iran'));
        }
    }
}