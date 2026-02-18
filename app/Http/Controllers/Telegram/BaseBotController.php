<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Lang;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Base Controller for Telegram Bot Flows
 *
 * Provides shared utilities for cache management, language handling,
 * and UI components used across all bot conversation flows.
 *
 * @package App\Http\Controllers\Telegram
 */
abstract class BaseBotController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CACHE & STATE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Generate cache key for user's current conversation step
     */
    protected function stepKey($chatId): string
    {
        return "bot_step_$chatId";
    }

    /**
     * Generate cache key for temporary user data field
     */
    protected function dataKey($chatId, string $field): string
    {
        return "bot_{$field}_{$chatId}";
    }

    /**
     * Generate cache key for user's language preference
     */
    protected function langKey($chatId): string
    {
        return "bot_lang_$chatId";
    }

    /**
     * Set user's current conversation step (persists for 24 hours)
     */
    protected function setStep($chatId, string $step): void
    {
        Cache::put($this->stepKey($chatId), $step, 86400);
    }

    /**
     * Get user's current conversation step
     */
    protected function getStep($chatId): ?string
    {
        return Cache::get($this->stepKey($chatId));
    }

    /**
     * Clear user's current conversation step
     */
    protected function clearStep($chatId): void
    {
        Cache::forget($this->stepKey($chatId));
    }

    /**
     * Set user's language preference (persists for 24 hours)
     */
    protected function setLang($chatId, string $lang): void
    {
        Cache::put($this->langKey($chatId), $lang, 86400);
    }

    /**
     * Get user's language preference with fallback to Uzbek
     */
    protected function getLang($chatId): string
    {
        return Cache::get($this->langKey($chatId), Lang::UZ);
    }

    /**
     * Store temporary user data (persists for 24 hours)
     */
    protected function put($chatId, string $field, $value): void
    {
        Cache::put($this->dataKey($chatId, $field), $value, 86400);
    }

    /**
     * Retrieve temporary user data
     */
    protected function get($chatId, string $field, $default = null)
    {
        return Cache::get($this->dataKey($chatId, $field), $default);
    }

    /**
     * Delete multiple cached fields at once
     */
    protected function forgetMany($chatId, array $fields): void
    {
        foreach ($fields as $f) {
            Cache::forget($this->dataKey($chatId, $f));
        }
    }

    /**
     * Get cached list (array) data
     */
    protected function getList($chatId, string $key): array
    {
        return (array)$this->get($chatId, $key, []);
    }

    /**
     * Append item to cached list
     */
    protected function pushList($chatId, string $key, array $item): void
    {
        $list = $this->getList($chatId, $key);
        $list[] = $item;
        $this->put($chatId, $key, $list);
    }

    /**
     * Get application ID from cache
     */
    protected function getAppId($chatId): ?int
    {
        return $this->get($chatId, 'application_id');
    }

    /**
     * Store application ID in cache
     */
    protected function setAppId($chatId, int $id): void
    {
        $this->put($chatId, 'application_id', $id);
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSLATION & LOCALIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Translate a key to the specified language
     */
    protected function t(string $lang, string $key): string
    {
        return __("bot.{$key}", [], $lang);
    }

    /*
    |--------------------------------------------------------------------------
    | UI HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Send message to user
     */
    protected function say($chatId, ?string $text, Keyboard $kb = null): ?int
    {
        $safe = trim((string)$text);
        if ($safe === '') {
            $safe = '…';
        }

        $params = ['chat_id' => $chatId, 'text' => $safe, 'parse_mode' => 'Markdown'];
        if ($kb) $params['reply_markup'] = $kb;

        $message = Telegram::sendMessage($params);
        return $message->getMessageId();
    }

    /**
     * Delete a message by its ID
     */
    protected function deleteMessage($chatId, int $messageId): void
    {
        try {
            Telegram::deleteMessage(['chat_id' => $chatId, 'message_id' => $messageId]);
        } catch (\Throwable $e) {
            // Silently ignore errors (message may already be deleted)
        }
    }

    /**
     * Store last question message ID to delete later
     */
    protected function trackQuestionMessage($chatId, ?int $messageId): void
    {
        if ($messageId) {
            $this->put($chatId, 'last_question_msg_id', $messageId);
        }
    }

    /**
     * Delete previous question message and answer if they exist
     */
    protected function deletePreviousQuestion($chatId): void
    {
        $lastQuestionId = $this->get($chatId, 'last_question_msg_id');
        $lastAnswerId = $this->get($chatId, 'last_answer_msg_id');

        if ($lastQuestionId) {
            $this->deleteMessage($chatId, $lastQuestionId);
        }
        if ($lastAnswerId) {
            $this->deleteMessage($chatId, $lastAnswerId);
        }

        $this->forgetMany($chatId, ['last_question_msg_id', 'last_answer_msg_id']);
    }

    /**
     * Generate simple "Back" button keyboard
     */
    protected function backKeyboard(string $lang): Keyboard
    {
        return Keyboard::make()->setResizeKeyboard(true)->row([$this->t($lang, 'btn_back')]);
    }

    /**
     * Find enum case by its label() method
     */
    protected function enumCaseByLabel(string $enumClass, string $label): ?\BackedEnum
    {
        foreach ($enumClass::cases() as $case) {
            if (method_exists($case, 'label') && $case->label() === $label) return $case;
        }
        return null;
    }

    /**
     * Find enum case by its localized() method
     */
    protected function enumCaseByLocalizedLabel(string $enumClass, string $label, string $lang): ?\BackedEnum
    {
        foreach ($enumClass::cases() as $case) {
            if (method_exists($case, 'localized') && $case->localized($lang) === $label) return $case;
        }
        return null;
    }
}