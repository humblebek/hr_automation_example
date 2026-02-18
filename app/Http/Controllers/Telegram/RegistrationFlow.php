<?php

namespace App\Http\Controllers\Telegram;

use App\Enum\Gender;
use App\Enum\Natinalities;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Registration Flow Controller
 *
 * Handles personal information collection flow:
 * full_name → phone → birth_date → gender → region → district → image → nationality
 *
 * @package App\Http\Controllers\Telegram
 */
class RegistrationFlow extends BaseBotController
{
    // Step constants
    public const STEP_FULL_NAME = 'ask_full_name';
    public const STEP_PHONE = 'ask_phone_number';
    public const STEP_BIRTHDATE = 'ask_birth_date';
    public const STEP_GENDER = 'ask_gender';
    public const STEP_REGION = 'ask_region';
    public const STEP_DISTRICT = 'ask_district';
    public const STEP_IMAGE = 'ask_image';
    public const STEP_NATIONALITY = 'ask_nationality';

    /**
     * Check if the given step belongs to registration flow
     */
    public static function isRegistrationStep(string $step): bool
    {
        return in_array($step, [
            self::STEP_FULL_NAME,
            self::STEP_PHONE,
            self::STEP_BIRTHDATE,
            self::STEP_GENDER,
            self::STEP_REGION,
            self::STEP_DISTRICT,
            self::STEP_IMAGE,
            self::STEP_NATIONALITY,
        ], true);
    }

    /**
     * Start registration flow
     */
    public function start($chatId): void
    {
        $this->askFullName($chatId);
    }

    /**
     * Handle registration flow steps
     */
    public function handle($chatId, $message): ?string
    {
        $lang = $this->getLang($chatId);
        $step = $this->getStep($chatId);

        // Full name step
        if ($step === self::STEP_FULL_NAME) {
            $full = trim($message->getText() ?? '');
            if ($full === '') {
                $this->say($chatId, "❗");
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'full_name', $full);
            $this->askPhone($chatId, $lang);
            return null;
        }

        // Phone number step
        if ($step === self::STEP_PHONE) {
            $contact = $message->getContact();
            if (!$contact) {
                $this->say($chatId, $this->t($lang, 'invalid_pick'));
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'phone', $contact->getPhoneNumber());
            $this->askBirthDate($chatId, $lang);
            return null;
        }

        // Birth date step
        if ($step === self::STEP_BIRTHDATE) {
            $d = trim($message->getText() ?? '');
            if (!$this->isValidDate($d)) {
                $this->say($chatId, $this->t($lang, 'invalid_date'));
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'birth_date', $d);
            $this->askGender($chatId, $lang);
            return null;
        }

        // Gender step
        if ($step === self::STEP_GENDER) {
            $text = trim($message->getText() ?? '');
            $genderValue = $this->parseGenderInput($text);

            if ($genderValue === null) {
                $this->say($chatId, $this->t($lang, 'invalid_pick'), $this->genderKeyboard($lang));
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'gender', $genderValue);
            $this->askRegion($chatId, $lang);
            return null;
        }

        // Region step
        if ($step === self::STEP_REGION) {
            $regionName = trim($message->getText() ?? '');
            $region = \App\Models\Region::where('name', $regionName)->first();

            if (!$region) {
                $this->say($chatId, $this->t($lang, 'invalid_region'));
                $this->askRegion($chatId, $lang);
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'region_id', $region->id);
            $this->askDistrict($chatId, $lang, $region->id);
            return null;
        }

        // District step
        if ($step === self::STEP_DISTRICT) {
            $districtName = trim($message->getText() ?? '');
            $regionId = $this->get($chatId, 'region_id');

            $district = \App\Models\District::where('region_id', $regionId)
                ->where('name', $districtName)
                ->first();

            if (!$district) {
                $this->say($chatId, $this->t($lang, 'invalid_district'));
                $this->askDistrict($chatId, $lang, $regionId);
                return null;
            }

            // Track answer message for deletion
            $answerMsgId = $message?->getMessageId();
            if ($answerMsgId) {
                $this->put($chatId, 'last_answer_msg_id', $answerMsgId);
            }

            // Delete previous question and answer
            $this->deletePreviousQuestion($chatId);

            $this->put($chatId, 'district_id', $district->id);
            $this->askImage($chatId, $lang);
            return null;
        }

        // Photo upload step
        if ($step === self::STEP_IMAGE) {
            if ($message->getText()) {
                $this->say($chatId, $this->t($lang, 'send_photo_only'));
                return null;
            }

            $fileId = null;
            $fileSize = null;
            $mimeType = null;

            // Try to get photo (sent as image, not document)
            $photos = $message->getPhoto();
            if ($photos && count($photos) > 0) {
                // Get the largest photo size (last in array)
                $photo = is_array($photos) ? end($photos) : $photos->last();

                if ($photo) {
                    $fileId = $photo->getFileId();
                    $fileSize = $photo->getFileSize();
                    $mimeType = 'image/jpeg'; // Telegram photos are always JPEG
                }
            }

            // Try document if no photo (sent as file)
            if (!$fileId && $message->getDocument()) {
                $doc = $message->getDocument();
                $mimeType = $doc->getMimeType() ?? '';
                $fileSize = $doc->getFileSize();

                // Only accept image files
                if (str_starts_with($mimeType, 'image/')) {
                    // Validate image format (JPG, PNG only)
                    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (in_array($mimeType, $allowedMimes)) {
                        $fileId = $doc->getFileId();
                    } else {
                        $this->say($chatId, $this->t($lang, 'invalid_image_format'));
                        return null;
                    }
                }
            }

            if (!$fileId) {
                $this->say($chatId, $this->t($lang, 'send_photo_file_only'));
                return null;
            }

            // Validate file size (5MB max)
            $maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if ($fileSize && $fileSize > $maxSize) {
                $this->say($chatId, $this->t($lang, 'image_too_large'));
                return null;
            }

            try {
                $file = Telegram::getFile(['file_id' => $fileId]);
                $filePath = $file->getFilePath();

                // Download from Telegram servers
                $fileUrl = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";
                $contents = @file_get_contents($fileUrl);

                if (!$contents) {
                    throw new \Exception("Empty image content");
                }

                // Determine file extension from mime type
                $extension = match($mimeType) {
                    'image/png' => 'png',
                    'image/jpg', 'image/jpeg' => 'jpg',
                    default => 'jpg',
                };

                $filename = 'applications/' . uniqid('photo_') . '.' . $extension;
                Storage::disk('public')->put($filename, $contents);

                $this->put($chatId, 'image', $filename);

                $this->say($chatId, $this->t($lang, 'photo_saved_next_nat'));
                $this->askNationality($chatId, $lang);
                return null;

            } catch (\Throwable $e) {
                Log::error("Telegram image save error: " . $e->getMessage());
                $this->say($chatId, $this->t($lang, 'image_upload_error'));
                return null;
            }
        }

        // Nationality step (final registration step)
        if ($step === self::STEP_NATIONALITY) {
            $label = trim($message->getText() ?? '');
            $case = $this->enumCaseByLocalizedLabel(Natinalities::class, $label, $lang);
            if (!$case) {
                $this->say($chatId, $this->t($lang, 'invalid_pick'));
                return null;
            }

            $this->put($chatId, 'nationality', $case->value);

            // Create Application record
            $fullName = (string)$this->get($chatId, 'full_name', '');
            $phone = (string)$this->get($chatId, 'phone', '');
            $birthDate = (string)$this->get($chatId, 'birth_date', '');
            $genderVal = (int)$this->get($chatId, 'gender', 0);
            $natVal = (int)$this->get($chatId, 'nationality', 0);
            $imagePath = (string)$this->get($chatId, 'image', '');

            $app = Application::create([
                'telegram_id' => $chatId,
                'full_name' => $fullName,
                'phone' => $phone,
                'birth_date' => $birthDate,
                'sex' => $genderVal,
                'region_id' => $this->get($chatId, 'region_id'),
                'district_id' => $this->get($chatId, 'district_id'),
                'nationality' => $natVal,
                'image' => $imagePath,
                'selected_lang' => $lang,
                'status_id' => \App\Models\Status::where('code', 100)->value('id'),
                'source_id' => Cache::get("bot_source_{$chatId}"),
            ]);

            $this->setAppId($chatId, $app->id);

            $this->say($chatId, $this->t($lang, 'reg_done'), Keyboard::remove());

            // Return next flow step (language skills)
            return 'language_skill';
        }

        return null;
    }

    /**
     * Reset registration flow data
     */
    public function reset($chatId): void
    {
        $this->clearStep($chatId);
        $this->forgetMany($chatId, ['full_name', 'phone', 'birth_date', 'gender', 'address', 'nationality', 'region_id', 'district_id', 'image']);
    }

    /*
    |--------------------------------------------------------------------------
    | PROMPT METHODS
    |--------------------------------------------------------------------------
    */

    private function askFullName($chatId): void
    {
        $lang = $this->getLang($chatId);
        $this->setStep($chatId, self::STEP_FULL_NAME);
        $msgId = $this->say($chatId, $this->t($lang, 'ask_fullname'), Keyboard::remove());
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askPhone($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_PHONE);

        $buttonLabel = $this->t($lang, 'btn_share_phone');

        $kb = Keyboard::make()
            ->row([
                Keyboard::button([
                    'text' => $buttonLabel,
                    'request_contact' => true,
                ]),
            ])
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $msgId = $this->say($chatId, $this->t($lang, 'ask_phone'), $kb);
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askBirthDate($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_BIRTHDATE);
        $msgId = $this->say($chatId, $this->t($lang, 'ask_birth'), Keyboard::remove());
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askGender($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_GENDER);
        $msgId = $this->say($chatId, $this->t($lang, 'ask_gender'), $this->genderKeyboard($lang));
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askRegion($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_REGION);

        $regions = \App\Models\Region::pluck('name')->toArray();
        $keyboard = Keyboard::make()->setResizeKeyboard(true);

        foreach (array_chunk($regions, 2) as $row) {
            $keyboard->row($row);
        }

        $msgId = $this->say($chatId, $this->t($lang, 'ask_region'), $keyboard);
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askDistrict($chatId, string $lang, int $regionId): void
    {
        $this->setStep($chatId, self::STEP_DISTRICT);

        $districts = \App\Models\District::where('region_id', $regionId)->pluck('name')->toArray();
        $keyboard = Keyboard::make()->setResizeKeyboard(true);

        foreach (array_chunk($districts, 2) as $row) {
            $keyboard->row($row);
        }

        $msgId = $this->say($chatId, $this->t($lang, 'ask_district'), $keyboard);
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askImage($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_IMAGE);
        $msgId = $this->say($chatId, $this->t($lang, 'ask_image'), Keyboard::remove());
        $this->trackQuestionMessage($chatId, $msgId);
    }

    private function askNationality($chatId, string $lang): void
    {
        $this->setStep($chatId, self::STEP_NATIONALITY);
        $labels = array_map(fn($c) => $c->localized($lang), Natinalities::cases());

        $kb = Keyboard::make()->setResizeKeyboard(true);
        foreach (array_chunk($labels, 2) as $row) $kb->row($row);

        $msgId = $this->say($chatId, $this->t($lang, 'ask_nat'), $kb);
        $this->trackQuestionMessage($chatId, $msgId);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    private function genderKeyboard(string $lang): Keyboard
    {
        [$male, $female] = $this->genderLabels($lang);

        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([$male, $female]);
    }

    private function genderLabels(string $lang): array
    {
        $male = match ($lang) {
            'ru' => 'Мужчина',
            'en' => 'Male',
            default => 'Erkak',
        };

        $female = match ($lang) {
            'ru' => 'Женщина',
            'en' => 'Female',
            default => 'Ayol',
        };

        return [$male, $female];
    }

    private function parseGenderInput(string $text): ?int
    {
        $norm = mb_strtolower(trim($text));
        $norm = preg_replace('/\s+/u', ' ', $norm);

        static $aliases = [
            'erkak' => Gender::MALE->value,
            'male' => Gender::MALE->value,
            'мужчина' => Gender::MALE->value,
            'ayol' => Gender::FEMALE->value,
            'female' => Gender::FEMALE->value,
            'женщина' => Gender::FEMALE->value,
        ];

        if (isset($aliases[$norm])) {
            return $aliases[$norm];
        }

        // Fallback: try last word
        $parts = preg_split('/\s+/u', $norm);
        $last = $parts ? end($parts) : null;

        return $last && isset($aliases[$last]) ? $aliases[$last] : null;
    }

    private function isValidDate(string $str): bool
    {
        try {
            $d = Carbon::createFromFormat('Y-m-d', $str);
            return $d && $d->format('Y-m-d') === $str;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
