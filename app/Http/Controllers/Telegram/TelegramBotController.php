<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Lang;
use App\Models\Source;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Telegram Bot Controller (Orchestrator)
 *
 * Main entry point for webhook updates. Routes messages to appropriate
 * flow controllers based on conversation state.
 *
 * @package App\Http\Controllers\Telegram
 */
class TelegramBotController extends BaseBotController
{
    // Step constants
    private const STEP_LANG = 'choose_lang';

    /**
     * Handle incoming webhook update from Telegram
     */
    public function handle(): void
    {
        $update = Telegram::getWebhookUpdate();
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();
        $chatId = $callback?->getMessage()?->getChat()?->getId()
            ?? $message?->getChat()?->getId();

        if (!$chatId) return;

        $callbackQuery = $update->getCallbackQuery();
        $callbackData = $callbackQuery?->getData();

        $text = trim($message->getText() ?? '');
        $step = $this->getStep($chatId);
        $lang = $this->getLang($chatId);

        // Handle /start command
        if (str_starts_with($text, '/start')) {
            $this->handleStart($chatId, $text);
            return;
        }

        // Check if session expired (no step found and user is not registered)
        // If cache expired, restart registration flow
        if (!$step && $text !== '') {
            $existingApp = \App\Models\Application::where('telegram_id', $chatId)->first();

            if (!$existingApp) {
                // Session expired for unregistered user - restart registration
                $this->say($chatId, $this->t(Lang::UZ, 'session_expired'));
                (new RegistrationFlow())->reset($chatId);
                $this->askLanguage($chatId);
                return;
            } else {
                // Registered user with no active session - show main menu
                $userLang = $existingApp->selected_lang ?? Lang::UZ;
                $this->setLang($chatId, $userLang);
                (new MainMenuFlow())->show($chatId, $userLang);
                return;
            }
        }

        // Language selection step
        if ($step === self::STEP_LANG) {
            $this->handleLanguagePick($chatId, $text);
            return;
        }

        // Global main menu handlers (work from any step)
        if ($this->handleGlobalMenuButtons($chatId, $text, $lang, $step)) {
            return;
        }

        // Handle callback queries (inline button presses)
        if ($callbackData && str_starts_with($callbackData, 'apply_')) {
            $occupationId = (int)substr($callbackData, 6);
            $departmentFlow = new DepartmentFlow();
            $msgIds = $departmentFlow->getOccupationMessageIds($chatId);
            $departmentFlow->clearOccupationMessageIds($chatId);
            (new QuestionFlow())->start($chatId, $occupationId, $msgIds);
            return;
        }

        // Occupation list step (ignore stray text, only Back button works)
        if ($step === DepartmentFlow::STEP_OCCUPATION_LIST) {
            return;
        }

        // Department selection step
        if ($step === DepartmentFlow::STEP_DEPT && $text !== '') {
            (new DepartmentFlow())->handleDepartmentPick($chatId, $text);
            return;
        }

        // Registration flow
        if (RegistrationFlow::isRegistrationStep($step)) {
            $nextFlow = (new RegistrationFlow())->handle($chatId, $message);
            if ($nextFlow === 'language_skill') {
                (new LanguageSkillFlow())->start($chatId);
            }
            return;
        }

        // Language skills flow
        if (LanguageSkillFlow::isLanguageSkillStep($step)) {
            $nextFlow = (new LanguageSkillFlow())->handle($chatId, $message);
            if ($nextFlow === 'education') {
                (new EducationFlow())->start($chatId);
            }
            return;
        }

        // Education flow
        if (EducationFlow::isEducationStep($step)) {
            $nextFlow = (new EducationFlow())->handle($chatId, $message);
            if ($nextFlow === 'experience') {
                (new ExperienceFlow())->start($chatId);
            }
            return;
        }

        // Experience flow
        if (ExperienceFlow::isExperienceStep($step)) {
            $nextFlow = (new ExperienceFlow())->handle($chatId, $message);
            if ($nextFlow === 'main_menu') {
                (new MainMenuFlow())->show($chatId, $lang);
            }
            return;
        }

        // Question flow
        if (QuestionFlow::isQuestionStep($step) && $text !== '') {
            $answerMessageId = $message?->getMessageId();
            $nextFlow = (new QuestionFlow())->handle($chatId, $text, $answerMessageId);
            if ($nextFlow === 'main_menu') {
                (new MainMenuFlow())->show($chatId, $lang);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INITIAL HANDLERS
    |--------------------------------------------------------------------------
    */

    /**
     * Handle /start command with optional deep link source tracking
     */
    private function handleStart($chatId, string $text): void
    {
        // Check if user already has an application
        $existingApp = \App\Models\Application::where('telegram_id', $chatId)->first();

        if ($existingApp) {
            // User already registered - show main menu directly
            $lang = $existingApp->selected_lang ?? $this->getLang($chatId);
            $this->setLang($chatId, $lang);
            $this->say($chatId, $this->t($lang, 'welcome_back'));
            (new MainMenuFlow())->show($chatId, $lang);
            return;
        }

        // New user - start registration
        (new RegistrationFlow())->reset($chatId);

        // Extract source code from deep link (e.g., "/start telegram_11")
        $parts = explode(' ', $text, 2);
        $sourceCode = $parts[1] ?? null;

        if ($sourceCode) {
            $source = Source::where('code', $sourceCode)->first();
            if ($source) {
                Cache::put("bot_source_{$chatId}", $source->id, 3600);
            }
        }

        $this->askLanguage($chatId);
    }

    /**
     * Show language selection keyboard
     */
    private function askLanguage($chatId): void
    {
        $this->setStep($chatId, self::STEP_LANG);

        $kb = Keyboard::make()->setResizeKeyboard(true);
        $labels = array_values(Lang::getList());
        foreach (array_chunk($labels, 2) as $row) $kb->row($row);

        $this->say($chatId, $this->t(Lang::UZ, 'welcome'), $kb);
    }

    /**
     * Handle user's language selection
     */
    private function handleLanguagePick($chatId, string $text): void
    {
        $code = null;
        foreach (Lang::getList() as $k => $v) {
            if ($v === $text) {
                $code = $k;
                break;
            }
        }
        if (!$code) {
            $this->say($chatId, $this->t(Lang::UZ, 'invalid_lang'));
            return;
        }

        $this->setLang($chatId, $code);
        (new RegistrationFlow())->start($chatId);
    }

    /*
    |--------------------------------------------------------------------------
    | GLOBAL MENU HANDLERS
    |--------------------------------------------------------------------------
    */

    /**
     * Handle global menu buttons (work from any step)
     * Returns true if handled, false otherwise
     */
    private function handleGlobalMenuButtons($chatId, string $text, string $lang, ?string $step): bool
    {
        $btnDepartments = $this->t($lang, 'btn_depart');
        $btnAbout = $this->t($lang, 'btn_about');
        $btnMyData = $this->t($lang, 'btn_mydata');
        $btnBack = $this->t($lang, 'btn_back');

        if ($text === $btnDepartments) {
            (new DepartmentFlow())->showDepartments($chatId, $lang);
            return true;
        }
        if ($text === $btnAbout) {
            (new MainMenuFlow())->showAbout($chatId, $lang);
            return true;
        }
        if ($text === $btnMyData) {
            (new MainMenuFlow())->showMyData($chatId, $lang);
            return true;
        }
        if ($text === $btnBack) {
            // Check if user is on occupation list step - delete occupation messages before going back
            if ($step === DepartmentFlow::STEP_OCCUPATION_LIST) {
                $departmentFlow = new DepartmentFlow();
                $msgIds = $departmentFlow->getOccupationMessageIds($chatId);

                // Delete all occupation cards
                foreach ($msgIds as $mid) {
                    $this->deleteMessage($chatId, $mid);
                }

                // Clear cached message IDs
                $departmentFlow->clearOccupationMessageIds($chatId);

                // Go back to department list
                $departmentFlow->showDepartments($chatId, $lang);
                return true;
            }

            // Default behavior - show main menu
            (new MainMenuFlow())->show($chatId, $lang);
            return true;
        }

        return false;
    }
}