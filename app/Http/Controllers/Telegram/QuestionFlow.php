<?php

namespace App\Http\Controllers\Telegram;

use App\Models\Application;
use App\Models\ApplicationOccupation;
use App\Models\Question;
use App\Models\Status;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Question Flow Controller
 *
 * Handles occupation-specific questionnaire:
 * - Loads questions for selected occupation
 * - Asks questions sequentially
 * - Saves answers as JSON in application_answers table
 *
 * @package App\Http\Controllers\Telegram
 */
class QuestionFlow extends BaseBotController
{
    // Step constant
    public const STEP_OCCUPATION_Q = 'occupation_questions';

    /**
     * Check if the given step belongs to question flow
     */
    public static function isQuestionStep(string $step): bool
    {
        return $step === self::STEP_OCCUPATION_Q;
    }

    /**
     * Start occupation-specific questionnaire
     */
    public function start($chatId, int $occupationId, array $messageIdsToDelete = []): void
    {
        $lang = $this->getLang($chatId);

        $application = Application::where('telegram_id', $chatId)->latest('id')->first();
        if (!$application) {
            $this->say($chatId, $this->t($lang, 'application_not_found'));
            return;
        }

        // Check if user has already applied to this occupation
        $alreadyApplied = ApplicationOccupation::where('application_id', $application->id)
            ->where('occupation_id', $occupationId)
            ->exists();

        if ($alreadyApplied) {
            // Delete occupation message cards
            foreach ($messageIdsToDelete as $mid) {
                try {
                    Telegram::deleteMessage(['chat_id' => $chatId, 'message_id' => $mid]);
                } catch (\Throwable $e) {
                }
            }

            // User has already applied - show message and return to main menu
            $this->say($chatId, $this->t($lang, 'already_applied'));
            (new MainMenuFlow())->show($chatId, $lang);
            return;
        }

        // Delete occupation message cards
        foreach ($messageIdsToDelete as $mid) {
            try {
                Telegram::deleteMessage(['chat_id' => $chatId, 'message_id' => $mid]);
            } catch (\Throwable $e) {
            }
        }

        $this->say($chatId, "✅ " . $this->t($lang, 'position_selected'), Keyboard::remove());

        // Load questions
        $questions = Question::where('occupation_id', $occupationId)
            ->orderBy('order')
            ->with(['questionTranslations' => fn($q) => $q->where('lang_code', $lang)])
            ->get();

        if ($questions->isEmpty()) {
            // Create ApplicationOccupation record even if no questions
            $defaultStatus = Status::where('code', 100)->first();

            if ($defaultStatus) {
                ApplicationOccupation::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'occupation_id' => $occupationId,
                    ],
                    [
                        'status_id' => $defaultStatus->id,
                    ]
                );
            }

            $this->say($chatId, $this->t($lang, 'no_questions'));
            $this->say($chatId, $this->t($lang, 'answers_saved'));
            // Show main menu even if no questions
            (new MainMenuFlow())->show($chatId, $lang);
            return;
        }

        // Cache question flow state
        $this->put($chatId, 'q_app_id', $application->id);
        $this->put($chatId, 'q_occupation_id', $occupationId);
        $this->put($chatId, 'q_index', 0);
        $this->put($chatId, 'q_list', $questions->map(fn($q) => [
            'id' => $q->id,
            'text' => $q->questionTranslations->first()?->text ?? ''
        ])->values()->all());

        $this->setStep($chatId, self::STEP_OCCUPATION_Q);

        $this->askCurrentQuestion($chatId);
    }

    /**
     * Handle occupation question answer
     */
    public function handle($chatId, string $answerText, ?int $answerMessageId = null): ?string
    {
        $appId = (int)$this->get($chatId, 'q_app_id');
        $qList = (array)$this->get($chatId, 'q_list', []);
        $idx = (int)$this->get($chatId, 'q_index', 0);

        if (!isset($qList[$idx]) || !$appId) {
            $this->finishQuestions($chatId);
            return 'main_menu';
        }

        $qId = (int)$qList[$idx]['id'];

        // Save answer as JSON
        \App\Models\ApplicationAnswer::create([
            'application_id' => $appId,
            'question_id' => $qId,
            'answer' => json_encode(['value' => $answerText], JSON_UNESCAPED_UNICODE),
        ]);

        // Track answer message ID for deletion
        if ($answerMessageId) {
            $this->put($chatId, 'last_answer_msg_id', $answerMessageId);
        }

        // Delete previous question and answer before showing next question
        $this->deletePreviousQuestion($chatId);

        // Move to next question
        $this->put($chatId, 'q_index', $idx + 1);
        $this->askCurrentQuestion($chatId);

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Display current question from the cached list
     */
    private function askCurrentQuestion($chatId): void
    {
        $qList = (array)$this->get($chatId, 'q_list', []);
        $idx = (int)$this->get($chatId, 'q_index', 0);

        if (!isset($qList[$idx])) {
            $this->finishQuestions($chatId);
            return;
        }

        $qText = $qList[$idx]['text'] ?: '—';
        $messageId = $this->say($chatId, "❓ " . $qText, Keyboard::remove());

        // Track question message for deletion later
        $this->trackQuestionMessage($chatId, $messageId);
    }

    /**
     * Complete questionnaire and return to main menu
     */
    private function finishQuestions($chatId): void
    {
        $lang = $this->getLang($chatId);

        // Get application and occupation IDs from cache before clearing
        $appId = (int)$this->get($chatId, 'q_app_id');
        $occupationId = (int)$this->get($chatId, 'q_occupation_id');

        // Create ApplicationOccupation record with default status (100 = Accepted)
        if ($appId && $occupationId) {
            $defaultStatus = Status::where('code', 100)->first();

            if ($defaultStatus) {
                ApplicationOccupation::firstOrCreate(
                    [
                        'application_id' => $appId,
                        'occupation_id' => $occupationId,
                    ],
                    [
                        'status_id' => $defaultStatus->id,
                    ]
                );
            }
        }

        $this->forgetMany($chatId, ['q_app_id', 'q_occupation_id', 'q_index', 'q_list']);
        $this->clearStep($chatId);

        $this->say($chatId, $this->t($lang, 'answers_saved'));

        // Show main menu after completing questions
        (new MainMenuFlow())->show($chatId, $lang);
    }
}