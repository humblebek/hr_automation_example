<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Experience Flow Controller
 *
 * Handles work experience collection (repeatable):
 * exp_where → exp_job → exp_duration → decide (add another or finish)
 * On finish, persists all child tables (languages, education, experience)
 *
 * @package App\Http\Controllers\Telegram
 */
class ExperienceFlow extends BaseBotController
{
    // Step constants
    public const STEP_EXP_WHERE = 'exp_where';
    public const STEP_EXP_JOB = 'exp_job';
    public const STEP_EXP_DURATION = 'exp_duration';
    public const STEP_EXP_DECIDE = 'exp_decide';

    /**
     * Check if the given step belongs to experience flow
     */
    public static function isExperienceStep(string $step): bool
    {
        return in_array($step, [
            self::STEP_EXP_WHERE,
            self::STEP_EXP_JOB,
            self::STEP_EXP_DURATION,
            self::STEP_EXP_DECIDE,
        ], true);
    }

    /**
     * Start experience flow
     */
    public function start($chatId): void
    {
        $lang = $this->getLang($chatId);
        $this->askExpWhere($chatId, $lang);
    }

    /**
     * Handle experience flow steps
     */
    public function handle($chatId, $message): ?string
    {
        $lang = $this->getLang($chatId);
        $step = $this->getStep($chatId);
        $text = trim($message->getText() ?? '');

        // Company name
        if ($step === self::STEP_EXP_WHERE) {
            if ($text === '') {
                $this->askExpWhere($chatId, $lang);
                return null;
            }
            $this->put($chatId, 'tmp_exp_where', $text);
            $this->askExpJob($chatId, $lang);
            return null;
        }

        // Job title
        if ($step === self::STEP_EXP_JOB) {
            if ($text === '') {
                $this->askExpJob($chatId, $lang);
                return null;
            }
            $this->put($chatId, 'tmp_exp_job', $text);
            $this->askExpDuration($chatId, $lang);
            return null;
        }

        // Duration
        if ($step === self::STEP_EXP_DURATION) {
            if ($text === '') {
                $this->askExpDuration($chatId, $lang);
                return null;
            }

            // Save to list
            $this->pushList($chatId, 'exp', [
                'where_work' => $this->get($chatId, 'tmp_exp_where'),
                'what_job' => $this->get($chatId, 'tmp_exp_job'),
                'duration' => $text,
            ]);
            $this->forgetMany($chatId, ['tmp_exp_where', 'tmp_exp_job', 'tmp_exp_duration']);

            $this->decideExp($chatId, $lang);
            return null;
        }

        // Decide whether to add another experience or finish
        if ($step === self::STEP_EXP_DECIDE) {
            $addAnother = in_array($text, ['➕ Ещё опыт', '➕ Add another job', '➕ Yana ish qo\'shish'], true);
            $finish = in_array($text, ['✅ Завершить', '✅ Finish', '✅ Yakunlash'], true);

            if ($addAnother) {
                $this->askExpWhere($chatId, $lang);
                return null;
            }
            if ($finish) {
                $this->persistChildTablesAndFinish($chatId, $lang);
                return 'main_menu';
            }

            $this->decideExp($chatId, $lang);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PROMPT METHODS
    |--------------------------------------------------------------------------
    */

    private function askExpWhere($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EXP_WHERE);
        $this->say($chatId, $lang === 'ru' ? 'Где работали (компания)?' : ($lang === 'en' ? 'Where did you work (company)?' : 'Qayerda ishlagansiz (kompaniya)?'), Keyboard::remove());
    }

    private function askExpJob($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EXP_JOB);
        $this->say($chatId, $lang === 'ru' ? 'Кем работали (должность)?' : ($lang === 'en' ? 'What was your role?' : 'Lavozimingiz nima edi?'));
    }

    private function askExpDuration($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EXP_DURATION);
        $this->say($chatId, $lang === 'ru' ? 'Срок работы (например: 2021-2023)' : ($lang === 'en' ? 'Duration (e.g., 2021-2023)' : 'Ish davomiyligi (masalan: 2021-2023)'));
    }

    private function decideExp($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EXP_DECIDE);
        $kb = Keyboard::make()->setResizeKeyboard(true)
            ->row(['➕ ' . ($lang === 'ru' ? 'Ещё опыт' : ($lang === 'en' ? 'Add another job' : 'Yana ish qo\'shish')),
                '✅ ' . ($lang === 'ru' ? 'Завершить' : ($lang === 'en' ? 'Finish' : 'Yakunlash'))]);
        $this->say($chatId, $lang === 'ru' ? 'Добавить ещё опыт?' : ($lang === 'en' ? 'Add another experience?' : 'Yana ish tajribasi qo\'shasizmi?'), $kb);
    }

    /*
    |--------------------------------------------------------------------------
    | DATA PERSISTENCE
    |--------------------------------------------------------------------------
    */

    /**
     * Save all collected child tables (languages, education, experience) to database
     */
    private function persistChildTablesAndFinish($chatId, string $lang): void
    {
        $appId = $this->getAppId($chatId);
        if (!$appId) {
            $this->say($chatId, $this->t($lang, 'session_error'));
            $this->clearStep($chatId);
            return;
        }

        $langskills = $this->getList($chatId, 'langskills');
        $educations = $this->getList($chatId, 'edu');
        $experiences = $this->getList($chatId, 'exp');

        try {
            // Save languages
            foreach ($langskills as $ls) {
                \App\Models\Language::create([
                    'application_id' => $appId,
                    'name' => $ls['name'],
                    'level' => $ls['level'],
                ]);
            }

            // Save educations
            foreach ($educations as $e) {
                \App\Models\Education::create([
                    'application_id' => $appId,
                    'study_level' => $e['study_level'],
                    'direction' => $e['direction'],
                    'where_study' => $e['where_study'],
                ]);
            }

            // Save experiences
            foreach ($experiences as $x) {
                \App\Models\Experience::create([
                    'application_id' => $appId,
                    'where_work' => $x['where_work'],
                    'what_job' => $x['what_job'],
                    'duration' => $x['duration'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Persist child tables error: ' . $e->getMessage());
        }

        $this->clearLists($chatId);

        $this->say($chatId, $lang === 'ru' ? 'Анкета сохранена. Спасибо!' : ($lang === 'en' ? 'Your profile has been saved. Thank you!' : 'Anketa saqlandi. Rahmat!'), Keyboard::remove());
    }

    /**
     * Clear all cached lists and temporary data
     */
    private function clearLists($chatId): void
    {
        $this->forgetMany($chatId, [
            'langskills', 'edu', 'exp',
            'tmp_lang_name', 'tmp_lang_level',
            'tmp_edu_level', 'tmp_edu_direction', 'tmp_edu_where',
            'tmp_exp_where', 'tmp_exp_job', 'tmp_exp_duration',
            'application_id'
        ]);
    }
}