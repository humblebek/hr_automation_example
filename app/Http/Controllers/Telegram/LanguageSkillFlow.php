<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Lang;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Language Skill Flow Controller
 *
 * Handles language proficiency collection (repeatable):
 * language_name → language_level → decide (add another or continue)
 *
 * @package App\Http\Controllers\Telegram
 */
class LanguageSkillFlow extends BaseBotController
{
    // Step constants
    public const STEP_LANGUAGE_NAME = 'ask_language_name';
    public const STEP_LANGUAGE_LEVEL = 'ask_language_level';
    public const STEP_LANGSKILL_DECIDE = 'langskill_decide';

    /**
     * Check if the given step belongs to language skill flow
     */
    public static function isLanguageSkillStep(string $step): bool
    {
        return in_array($step, [
            self::STEP_LANGUAGE_NAME,
            self::STEP_LANGUAGE_LEVEL,
            self::STEP_LANGSKILL_DECIDE,
        ], true);
    }

    /**
     * Start language skill flow
     */
    public function start($chatId): void
    {
        $lang = $this->getLang($chatId);
        $this->askLanguageName($chatId, $lang);
    }

    /**
     * Handle language skill flow steps
     */
    public function handle($chatId, $message): ?string
    {
        $lang = $this->getLang($chatId);
        $step = $this->getStep($chatId);
        $text = trim($message->getText() ?? '');

        // Language name selection
        if ($step === self::STEP_LANGUAGE_NAME) {
            if ($text === '') {
                $this->askLanguageName($chatId, $lang);
                return null;
            }

            // Find language code by localized label
            $langCode = collect(Lang::getList($lang))->search($text);
            if ($langCode === false) {
                $this->askLanguageName($chatId, $lang);
                return null;
            }

            $this->put($chatId, 'tmp_lang_name', $langCode);
            $this->askLanguageLevel($chatId, $lang, $langCode);
            return null;
        }

        // Language level selection
        if ($step === self::STEP_LANGUAGE_LEVEL) {
            if ($text === '') {
                $this->askLanguageLevel($chatId, $lang, (string)$this->get($chatId, 'tmp_lang_name'));
                return null;
            }

            // Find level code by localized label
            $levelCode = collect(\App\Enum\LanguageLevel::getList($lang))->search($text, true);
            if ($levelCode === false) {
                $this->askLanguageLevel($chatId, $lang, (string)$this->get($chatId, 'tmp_lang_name'));
                return null;
            }

            $this->put($chatId, 'tmp_lang_level', $levelCode);

            // Save to list
            $this->pushList($chatId, 'langskills', [
                'name' => (string)$this->get($chatId, 'tmp_lang_name'),
                'level' => (string)$this->get($chatId, 'tmp_lang_level'),
            ]);

            $this->forgetMany($chatId, ['tmp_lang_name', 'tmp_lang_level']);

            $this->decideLangSkill($chatId, $lang);
            return null;
        }

        // Decide whether to add another language
        if ($step === self::STEP_LANGSKILL_DECIDE) {
            $addAnother = ($text === $this->t($lang, 'btn_add_lang'));
            $continue = ($text === $this->t($lang, 'btn_continue'));

            if ($addAnother) {
                $this->askLanguageName($chatId, $lang);
                return null;
            }
            if ($continue) {
                return 'education';
            }

            $this->decideLangSkill($chatId, $lang);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PROMPT METHODS
    |--------------------------------------------------------------------------
    */

    private function askLanguageName($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_LANGUAGE_NAME);

        $keyboard = Keyboard::make()->setResizeKeyboard(true);

        foreach (Lang::getList($lang) as $label) {
            $keyboard->row([Keyboard::button($label)]);
        }

        $this->say($chatId, $this->t($lang, 'ask_language_name'), $keyboard);
    }

    private function askLanguageLevel($chatId, string $lang, string $selectedLang): void
    {
        $this->setStep($chatId, self::STEP_LANGUAGE_LEVEL);
        $this->put($chatId, 'selected_language', $selectedLang);

        $keyboard = Keyboard::make()->setResizeKeyboard(true);
        foreach (\App\Enum\LanguageLevel::getList($lang) as $label) {
            $keyboard->row([Keyboard::button($label)]);
        }

        $langName = $this->displayLangName($selectedLang, $lang);
        $this->say($chatId, sprintf($this->t($lang, 'ask_language_level'), $langName), $keyboard);
    }

    private function decideLangSkill($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_LANGSKILL_DECIDE);

        $kb = Keyboard::make()->setResizeKeyboard(true)
            ->row([
                $this->t($lang, 'btn_add_lang'),
                $this->t($lang, 'btn_continue'),
            ]);

        $this->say($chatId, $this->t($lang, 'add_another_lang_q'), $kb);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    private function displayLangName(string $code, string $uiLang): string
    {
        return match ($uiLang) {
            'ru' => match ($code) {
                Lang::UZ => "узбекский",
                Lang::RU => "русский",
                Lang::EN => "английский",
                default => strtoupper($code),
            },
            'en' => match ($code) {
                Lang::UZ => "Uzbek",
                Lang::RU => "Russian",
                Lang::EN => "English",
                default => strtoupper($code),
            },
            default => match ($code) {
                Lang::UZ => "O'zbek tili",
                Lang::RU => "Rus tili",
                Lang::EN => "Ingliz tili",
                default => strtoupper($code),
            },
        };
    }
}