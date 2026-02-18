<?php

namespace App\Observers;

use App\Models\ApplicationOccupation;
use App\Models\StatusTranslation;
use App\Models\OccupationTranslation;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class ApplicationOccupationObserver
{
    /**
     * Handle the ApplicationOccupation "created" event.
     */
    public function created(ApplicationOccupation $applicationOccupation): void
    {
        //
    }

    /**
     * Handle the ApplicationOccupation "updated" event.
     */
    public function updated(ApplicationOccupation $applicationOccupation): void
    {
        // Only send message if status_id changed
        if (! $applicationOccupation->wasChanged('status_id')) {
            return;
        }

        // Get the application to access user's telegram_id and language
        $application = $applicationOccupation->application;
        if (! $application) {
            Log::warning("ApplicationOccupation #{$applicationOccupation->id} has no associated application");
            return;
        }

        // Detect user's preferred language (default: uz)
        $lang = $application->selected_lang ?? 'uz';

        // Find the translation for the new status
        $statusTranslation = StatusTranslation::where('status_id', $applicationOccupation->status_id)
            ->where('lang_code', $lang)
            ->first();

        if (! $statusTranslation) {
            Log::warning("No status translation found for status_id {$applicationOccupation->status_id} / lang {$lang}");
            return;
        }

        // Get occupation name in user's language
        $occupationTranslation = OccupationTranslation::where('occupation_id', $applicationOccupation->occupation_id)
            ->where('lang_code', $lang)
            ->first();

        $occupationName = $occupationTranslation?->title ?? 'N/A';

        // Prepare message with occupation name
        $message = "📋 *" . $occupationName . "*\n\n" . $statusTranslation->message;

        // Send Telegram message
        try {
            Telegram::sendMessage([
                'chat_id'    => $application->telegram_id,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send Telegram status message for ApplicationOccupation #{$applicationOccupation->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the ApplicationOccupation "deleted" event.
     */
    public function deleted(ApplicationOccupation $applicationOccupation): void
    {
        //
    }

    /**
     * Handle the ApplicationOccupation "restored" event.
     */
    public function restored(ApplicationOccupation $applicationOccupation): void
    {
        //
    }

    /**
     * Handle the ApplicationOccupation "force deleted" event.
     */
    public function forceDeleted(ApplicationOccupation $applicationOccupation): void
    {
        //
    }
}