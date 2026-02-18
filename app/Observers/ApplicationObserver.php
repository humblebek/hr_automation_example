<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\StatusTranslation;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        // Only send message if status_id changed
        if (! $application->wasChanged('status_id')) {
            return;
        }

        // Detect user's preferred language (default: uz)
        $lang = $application->selected_lang ?? 'uz';

        // Find the translation for the new status
        $translation = StatusTranslation::where('status_id', $application->status_id)
            ->where('lang_code', $lang)
            ->first();

        if (! $translation) {
            Log::warning("No status translation found for status_id {$application->status_id} / lang {$lang}");
            return;
        }

        // Prepare message
        $message = $translation->message;

        // Send Telegram message
        try {
            Telegram::sendMessage([
                'chat_id'    => $application->telegram_id,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send Telegram status message: " . $e->getMessage());
        }
    }

    /**
     * Handle the Application "deleted" event.
     */
    public function deleted(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "restored" event.
     */
    public function restored(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "force deleted" event.
     */
    public function forceDeleted(Application $application): void
    {
        //
    }
}
