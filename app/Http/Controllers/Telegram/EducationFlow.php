<?php

namespace App\Http\Controllers\Telegram;

use Telegram\Bot\Keyboard\Keyboard;

/**
 * Education Flow Controller
 *
 * Handles education collection (repeatable):
 * edu_level → edu_direction → edu_where → decide (add another or continue)
 *
 * @package App\Http\Controllers\Telegram
 */
class EducationFlow extends BaseBotController
{
    // Step constants
    public const STEP_EDU_LEVEL = 'edu_level';
    public const STEP_EDU_DIRECTION = 'edu_direction';
    public const STEP_EDU_WHERE = 'edu_where';
    public const STEP_EDU_DECIDE = 'edu_decide';

    /**
     * Check if the given step belongs to education flow
     */
    public static function isEducationStep(string $step): bool
    {
        return in_array($step, [
            self::STEP_EDU_LEVEL,
            self::STEP_EDU_DIRECTION,
            self::STEP_EDU_WHERE,
            self::STEP_EDU_DECIDE,
        ], true);
    }

    /**
     * Start education flow
     */
    public function start($chatId): void
    {
        $lang = $this->getLang($chatId);
        $this->askEduLevel($chatId, $lang);
    }

    /**
     * Handle education flow steps
     */
    public function handle($chatId, $message): ?string
    {
        $lang = $this->getLang($chatId);
        $step = $this->getStep($chatId);
        $text = trim($message->getText() ?? '');

        // Education level selection
        if ($step === self::STEP_EDU_LEVEL) {
            if ($text === '') {
                $this->askEduLevel($chatId, $lang);
                return null;
            }
            $this->put($chatId, 'tmp_edu_level', $text);
            $this->askEduDirection($chatId, $lang);
            return null;
        }

        // Education direction/major
        if ($step === self::STEP_EDU_DIRECTION) {
            if ($text === '') {
                $this->askEduDirection($chatId, $lang);
                return null;
            }
            $this->put($chatId, 'tmp_edu_direction', $text);
            $this->askEduWhere($chatId, $lang);
            return null;
        }

        // Institution name
        if ($step === self::STEP_EDU_WHERE) {
            if ($text === '') {
                $this->askEduWhere($chatId, $lang);
                return null;
            }
            $this->put($chatId, 'tmp_edu_where', $text);

            // Save to list
            $this->pushList($chatId, 'edu', [
                'study_level' => $this->get($chatId, 'tmp_edu_level'),
                'direction' => $this->get($chatId, 'tmp_edu_direction'),
                'where_study' => $this->get($chatId, 'tmp_edu_where'),
            ]);
            $this->forgetMany($chatId, ['tmp_edu_level', 'tmp_edu_direction', 'tmp_edu_where']);

            $this->decideEdu($chatId, $lang);
            return null;
        }

        // Decide whether to add another education
        if ($step === self::STEP_EDU_DECIDE) {
            $addAnother = in_array($text, ['➕ Ещё образование', '➕ Add another education', '➕ Yana ta\'lim qo\'shish'], true);
            $continue = in_array($text, ['✅ Продолжить', '✅ Continue', '✅ Davom etish'], true);

            if ($addAnother) {
                $this->askEduLevel($chatId, $lang);
                return null;
            }
            if ($continue) {
                return 'experience';
            }

            $this->decideEdu($chatId, $lang);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PROMPT METHODS
    |--------------------------------------------------------------------------
    */

    private function askEduLevel($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EDU_LEVEL);
        $kb = Keyboard::make()->setResizeKeyboard(true);
        $levels = $lang === 'ru'
            ? ['Среднее', 'Среднее-спец', 'Незаконченное высшее', 'Бакалавр', 'Магистр']
            : ($lang === 'en'
                ? ['Secondary', 'College', 'Incomplete higher education', 'Bachelor', 'Master']
                : ['O‘rta', 'O‘rta-maxsus', 'Tugallanmagan oliy', 'Bakalavr', 'Magistr']);

        foreach (array_chunk($levels, 2) as $row) $kb->row($row);
        $this->say($chatId, $lang === 'ru' ? 'Выберите уровень образования' : ($lang === 'en' ? 'Choose study level' : 'Ta\'lim darajasini tanlang'), $kb);
    }

    private function askEduDirection($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EDU_DIRECTION);
        $this->say($chatId, $lang === 'ru' ? 'Направление/специальность:' : ($lang === 'en' ? 'Field / major:' : 'Yo\'nalish / mutaxassislik:'), Keyboard::remove());
    }

    private function askEduWhere($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EDU_WHERE);
        $this->say($chatId, $lang === 'ru' ? 'Где обучались?' : ($lang === 'en' ? 'Where did you study?' : 'Qayerda o\'qigansiz?'));
    }

    private function decideEdu($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_EDU_DECIDE);
        $kb = Keyboard::make()->setResizeKeyboard(true)
            ->row(['➕ ' . ($lang === 'ru' ? 'Ещё образование' : ($lang === 'en' ? 'Add another education' : 'Yana ta\'lim qo\'shish')),
                '✅ ' . ($lang === 'ru' ? 'Продолжить' : ($lang === 'en' ? 'Continue' : 'Davom etish'))]);
        $this->say($chatId, $lang === 'ru' ? 'Добавить ещё образование?' : ($lang === 'en' ? 'Add another education?' : 'Yana ta\'lim qo\'shasizmi?'), $kb);
    }
}
