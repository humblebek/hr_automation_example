<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Gender;
use App\Enum\Lang;
use App\Enum\Natinalities;
use App\Models\Application;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Main Menu Flow Controller
 *
 * Handles main menu navigation and info screens:
 * - Main menu display
 * - About us information
 * - My data summary
 *
 * @package App\Http\Controllers\Telegram
 */
class MainMenuFlow extends BaseBotController
{
    public const STEP_MAIN = 'step_main';

    /**
     * Show main menu with navigation buttons
     */
    public function show($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_MAIN);
        $this->say($chatId, $this->t($lang, 'menu'), $this->mainMenuKeyboard($lang));
    }

    /**
     * Show "About Us" information
     */
    public function showAbout($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_MAIN);
        $this->say($chatId, $this->t($lang, 'about_text'), $this->backKeyboard($lang));
    }

    /**
     * Show user's application data summary
     */
    public function showMyData($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_MAIN);

        $app = Application::with(['region', 'district', 'status.statusTranslations'])
            ->where('telegram_id', $chatId)
            ->latest('id')
            ->first();

        if (!$app) {
            $this->say($chatId, $this->t($lang, 'mydata_none'), $this->mainMenuKeyboard($lang));
            return;
        }

        // Format values
        $sexLabel = $this->genderUiLabel((int)$app->sex, $lang);
        $natLabel = $this->nationalityUiLabel((int)$app->nationality, $lang);
        $region = $app->region?->name ?? '—';
        $district = $app->district?->name ?? '—';
        $status = $app->status?->statusTranslations
            ->where('lang_code', $lang)
            ->first()?->name ?? '—';
        $selectedLangName = $this->displayLangName((string)$app->selected_lang, $lang);

        // Get labels
        $title = $this->t($lang, 'mydata_title');
        $lblName = $this->t($lang, 'lbl_fullname');
        $lblPhone = $this->t($lang, 'lbl_phone');
        $lblBirth = $this->t($lang, 'lbl_birth');
        $lblGender = $this->t($lang, 'lbl_gender');
        $lblNat = $this->t($lang, 'lbl_national');
        $lblRegion = $this->t($lang, 'lbl_region');
        $lblLang = $this->t($lang, 'lbl_lang');
        $lblStatus = $this->t($lang, 'lbl_status');
        $lblCreated = $this->t($lang, 'lbl_created');

        $txt = "{$title}\n\n"
            . "🧾 *{$lblName}:* {$app->full_name}\n"
            . "📞 *{$lblPhone}:* {$app->phone}\n"
            . "🎂 *{$lblBirth}:* {$app->birth_date}\n"
            . "⚧ *{$lblGender}:* {$sexLabel}\n"
            . "🌍 *{$lblNat}:* {$natLabel}\n"
            . "🏙 *{$lblRegion}:* {$region}, {$district}\n"
            . "💬 *{$lblLang}:* {$selectedLangName}\n"
            . "📋 *{$lblStatus}:* {$status}\n"
            . "🕒 *{$lblCreated}:* " . $app->created_at->format('Y-m-d');

        $this->say($chatId, $txt, $this->backKeyboard($lang));
    }

    /*
    |--------------------------------------------------------------------------
    | UI HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Generate main menu keyboard
     */
    private function mainMenuKeyboard(string $lang): Keyboard
    {
        $kb = Keyboard::make()->setResizeKeyboard(true);
        $kb->row([$this->t($lang, 'btn_depart'), $this->t($lang, 'btn_about')]);
        $kb->row([$this->t($lang, 'btn_mydata')]);
        return $kb;
    }

    /**
     * Get localized gender label for display
     */
    private function genderUiLabel(int $val, string $lang): string
    {
        return match ($lang) {
            'ru' => match ($val) {
                Gender::MALE->value => 'Мужчина',
                Gender::FEMALE->value => 'Женщина',
                default => '—',
            },
            'en' => match ($val) {
                Gender::MALE->value => 'Male',
                Gender::FEMALE->value => 'Female',
                default => '—',
            },
            default => match ($val) {
                Gender::MALE->value => 'Erkak',
                Gender::FEMALE->value => 'Ayol',
                default => '—',
            },
        };
    }

    /**
     * Get localized nationality label for display
     */
    private function nationalityUiLabel(int $val, string $lang): string
    {
        $uz = [
            Natinalities::UZBEK->value => "O'zbek",
            Natinalities::RUS->value => "Rus",
            Natinalities::TAJIK->value => "Tojik",
            Natinalities::QIRGIZ->value => "Qirg'iz",
            Natinalities::INGLIZ->value => "Ingliz",
            Natinalities::KOZOQ->value => "Qozoq",
            Natinalities::ARMAN->value => "Arman",
        ];
        $ru = [
            Natinalities::UZBEK->value => "Узбек",
            Natinalities::RUS->value => "Русский",
            Natinalities::TAJIK->value => "Таджик",
            Natinalities::QIRGIZ->value => "Киргиз",
            Natinalities::INGLIZ->value => "Англичанин",
            Natinalities::KOZOQ->value => "Казах",
            Natinalities::ARMAN->value => "Армянин",
        ];
        $en = [
            Natinalities::UZBEK->value => "Uzbek",
            Natinalities::RUS->value => "Russian",
            Natinalities::TAJIK->value => "Tajik",
            Natinalities::QIRGIZ->value => "Kyrgyz",
            Natinalities::INGLIZ->value => "English",
            Natinalities::KOZOQ->value => "Kazakh",
            Natinalities::ARMAN->value => "Armenian",
        ];

        return match ($lang) {
            'ru' => $ru[$val] ?? '—',
            'en' => $en[$val] ?? '—',
            default => $uz[$val] ?? '—',
        };
    }

    /**
     * Display language name in user's interface language
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
