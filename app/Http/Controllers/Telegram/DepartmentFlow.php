<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Status;
use App\Models\Department;
use App\Models\Occupation;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Department Flow Controller
 *
 * Handles department browsing and occupation selection:
 * - Shows list of departments
 * - User selects department
 * - Shows occupations with inline "Apply" buttons
 *
 * @package App\Http\Controllers\Telegram
 */
class DepartmentFlow extends BaseBotController
{
    // Step constants
    public const STEP_DEPT = 'department';
    public const STEP_OCCUPATION_LIST = 'occupation_list';

    /**
     * Check if the given step belongs to department flow
     */
    public static function isDepartmentStep(string $step): bool
    {
        return in_array($step, [
            self::STEP_DEPT,
            self::STEP_OCCUPATION_LIST,
        ], true);
    }

    /**
     * Show list of active departments
     */
    public function showDepartments($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_DEPT);

        $deps = Department::where('status', Status::ACTIVE->value)
            ->with(['departmentTranslations' => fn($q) => $q->where('lang_code', $lang)])
            ->get();

        if ($deps->isEmpty()) {
            $this->say($chatId, $this->t($lang, 'depart_none'));
            return;
        }

        $kb = Keyboard::make()->setResizeKeyboard(true);
        $names = $deps->map(fn($d) => $d->departmentTranslations->first()?->name ?? '—')->toArray();
        foreach (array_chunk($names, 2) as $row) $kb->row($row);
        $kb->row([$this->t($lang, 'btn_back')]);

        $this->say($chatId, $this->t($lang, 'depart_choose'), $kb);
    }

    /**
     * Handle department selection and show available occupations
     */
    public function handleDepartmentPick($chatId, string $typedName): ?string
    {
        $lang = $this->getLang($chatId);

        // Find department by translated name
        $department = Department::whereHas('departmentTranslations', fn($q) => $q
            ->where('lang_code', $lang)
            ->where('name', $typedName)
        )->first();

        if (!$department) {
            $this->say($chatId, $this->t($lang, 'invalid_pick'));
            $this->showDepartments($chatId, $lang);
            return null;
        }

        // Load occupations for this department
        $occupations = Occupation::where('department_id', $department->id)
            ->where('status', Status::ACTIVE->value)
            ->with(['occupationTranslations' => fn($q) => $q->where('lang_code', $lang)])
            ->get();

        if ($occupations->isEmpty()) {
            $this->say($chatId, $this->t($lang, 'depart_none'), $this->backKeyboard($lang));
            return null;
        }

        // Set step first
        $this->setStep($chatId, self::STEP_OCCUPATION_LIST);

        // Show loading message with back keyboard
        $loadingMsgId = $this->say($chatId, $this->t($lang, 'loading_vacancies'), $this->backKeyboard($lang));

        $sentIds = [$loadingMsgId];

        // Send occupation cards
        foreach ($occupations as $occ) {
            $title = $occ->occupationTranslations->first()?->title ?? '—';
            $rawDesc = $occ->occupationTranslations->first()?->description ?? '—';

            // Strip HTML tags from RichEditor content and decode entities
            $desc = html_entity_decode(strip_tags($rawDesc), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $desc = trim($desc) ?: '—';

            $caption = "🧑‍💼 *{$title}*\n\n{$desc}";

            $inline = Keyboard::make([
                'inline_keyboard' => [
                    [['text' => $this->t($lang, 'apply'), 'callback_data' => 'apply_' . $occ->id]],
                ]
            ]);

            $photoPath = public_path("storage/{$occ->photo}");

            if ($occ->photo && is_file($photoPath)) {
                $sent = Telegram::sendPhoto([
                    'chat_id' => $chatId,
                    'photo' => InputFile::create($photoPath),
                    'caption' => $caption,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $inline,
                ]);
            } else {
                $sent = Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $caption,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $inline,
                ]);
            }

            $sentIds[] = $sent->getMessageId();
        }

        // Store message IDs for cleanup later
        $this->put($chatId, 'occ_msg_ids', $sentIds);

        return 'occupation_list';
    }

    /**
     * Get stored occupation message IDs
     */
    public function getOccupationMessageIds($chatId): array
    {
        return (array)$this->get($chatId, 'occ_msg_ids', []);
    }

    /**
     * Clear occupation message IDs from cache
     */
    public function clearOccupationMessageIds($chatId): void
    {
        $this->forgetMany($chatId, ['occ_msg_ids']);
    }
}
