<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\StatusTranslation;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $statuses = [
                // ─────────────── Occupation-level (per vacancy) ───────────────
                [
                    'code' => 100, 'color' => 'success',
                    'name_uz' => 'Qabul qilindi', 'name_ru' => 'Принято', 'name_en' => 'Accepted',
                    'message_uz' => 'Siz ushbu vakansiya bo‘yicha qabul qilindingiz ✅',
                    'message_ru' => 'Вы приняты на эту вакансию ✅',
                    'message_en' => 'You have been accepted for this vacancy ✅',
                ],
                [
                    'code' => 200, 'color' => 'danger',
                    'name_uz' => 'Qabul qilinmadi', 'name_ru' => 'Отклонено', 'name_en' => 'Rejected',
                    'message_uz' => 'Afsuski, ushbu vakansiya bo‘yicha arizangiz rad etildi ❌',
                    'message_ru' => 'К сожалению, заявка по этой вакансии отклонена ❌',
                    'message_en' => 'Unfortunately, your application for this vacancy was rejected ❌',
                ],
                [
                    'code' => 300, 'color' => 'secondary',
                    'name_uz' => 'Zahiraga olindi', 'name_ru' => 'В резерве', 'name_en' => 'On hold',
                    'message_uz' => 'Arizangiz zahiraga olindi 🕒',
                    'message_ru' => 'Ваша заявка помещена в резерв 🕒',
                    'message_en' => 'Your application has been put on hold 🕒',
                ],
                [
                    'code' => 400, 'color' => 'info',
                    'name_uz' => 'Intervyuga chaqirildi', 'name_ru' => 'Приглашён на собеседование', 'name_en' => 'Invited to interview',
                    'message_uz' => 'Siz suhbatga chaqirildingiz 🗓️',
                    'message_ru' => 'Вы приглашены на собеседование 🗓️',
                    'message_en' => 'You are invited to an interview 🗓️',
                ],
                [
                    'code' => 500, 'color' => 'warning',
                    'name_uz' => 'Intervyuga kelmadi', 'name_ru' => 'Не пришёл на собеседование', 'name_en' => 'Did not attend',
                    'message_uz' => 'Siz suhbatga kelmadingiz ⚠️',
                    'message_ru' => 'Вы не пришли на собеседование ⚠️',
                    'message_en' => 'You did not attend the interview ⚠️',
                ],
                [
                    'code' => 600, 'color' => 'danger',
                    'name_uz' => 'Intervyudan yiqildi', 'name_ru' => 'Не прошёл собеседование', 'name_en' => 'Failed interview',
                    'message_uz' => 'Afsuski, suhbatdan o‘ta olmadingiz ❌',
                    'message_ru' => 'К сожалению, вы не прошли собеседование ❌',
                    'message_en' => 'Unfortunately, you did not pass the interview ❌',
                ],

                // ─────────────── Application-level (global) ───────────────
                [
                    'code' => 800, 'color' => 'info',
                    'name_uz' => 'Jarayonda', 'name_ru' => 'В процессе', 'name_en' => 'In process',
                    'message_uz' => 'Arizangiz qabul qilindi va ko‘rib chiqilmoqda 🕒',
                    'message_ru' => 'Ваша заявка принята и находится на рассмотрении 🕒',
                    'message_en' => 'Your application has been received and is under review 🕒',
                ],
                [
                    'code' => 900, 'color' => 'success',
                    'name_uz' => 'Ishga qabul qilindi', 'name_ru' => 'Принят на работу', 'name_en' => 'Got a job',
                    'message_uz' => 'Tabriklaymiz! Siz ishga qabul qilindingiz 🎉',
                    'message_ru' => 'Поздравляем! Вы приняты на работу 🎉',
                    'message_en' => 'Congratulations! You got the job 🎉',
                ],
                [
                    'code' => 901, 'color' => 'danger',
                    'name_uz' => 'Ishga qabul qilinmadi', 'name_ru' => 'Не принят на работу', 'name_en' => 'Not hired',
                    'message_uz' => 'Afsuski, siz ishga qabul qilinmadingiz ❌',
                    'message_ru' => 'К сожалению, вы не приняты на работу ❌',
                    'message_en' => 'Unfortunately, you were not hired ❌',
                ],
            ];

            foreach ($statuses as $data) {
                $status = Status::updateOrCreate(
                    ['code' => $data['code']],
                    ['color' => $data['color']]
                );

                $translations = [
                    ['lang_code' => 'uz', 'name' => $data['name_uz'], 'message' => $data['message_uz']],
                    ['lang_code' => 'ru', 'name' => $data['name_ru'], 'message' => $data['message_ru']],
                    ['lang_code' => 'en', 'name' => $data['name_en'], 'message' => $data['message_en']],
                ];

                foreach ($translations as $t) {
                    StatusTranslation::updateOrCreate(
                        ['status_id' => $status->id, 'lang_code' => $t['lang_code']],
                        ['name' => $t['name'], 'message' => $t['message']]
                    );
                }
            }
        });
    }
}
