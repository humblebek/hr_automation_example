<?php

namespace Database\Seeders;

use App\Enum\QuestionFile;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Occupation;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $optionalQuestions = [
            [
                'order' => 1,
                'question_type' => QuestionFile::TEXT->value,
                'translations' => [
                    'uz' => "To'liq ism-familiyangizni kiriting",
                    'ru' => 'Введите ваше полное имя и фамилию',
                    'en' => 'Enter your full name and surname',
                ],
            ],
            [
                'order' => 2,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => "Tug'ilgan sanangizni kiriting",
                    'ru' => 'Введите вашу дату рождения',
                    'en' => 'Enter your date of birth',
                ],
            ],
            [
                'order' => 3,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Jinsingizni tanlang',
                    'ru' => 'Выберите ваш пол',
                    'en' => 'Select your gender',
                ],
            ],
            [
                'order' => 4,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Qaysi millatdansiz?',
                    'ru' => 'К какой национальности вы относитесь?',
                    'en' => 'What is your nationality?',
                ],
            ],
            [
                'order' => 5,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Telefon raqamingizni kiriting',
                    'ru' => 'Введите свой номер телефона',
                    'en' => 'Enter your phone number',
                ],
            ],
            [
                'order' => 6,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Viloyatingizni tanlang',
                    'ru' => 'Выберите вашу область',
                    'en' => 'Select your region',
                ],
            ],
            [
                'order' => 7,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Tumaningizni tanlang',
                    'ru' => 'Выберите ваш район',
                    'en' => 'Select your district',
                ],
            ],
            [
                'order' => 8,
                'question_type' => QuestionFile::IMAGE->value,
                'translations' => [
                    'uz' => 'Rasmingizni kiriting — yuzingiz tiniq tushgan, yelkadan tepasi ko‘rinadigan bo‘lsin.',
                    'ru' => 'Загрузите своё фото — лицо должно быть чётко видно, кадр с плеч и выше.',
                    'en' => 'Upload your photo — your face should be clearly visible, from shoulders and above.',
                ],
            ],
            [
                'order' => 9,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => 'Qaysi tillarni bilasiz?',
                    'ru' => 'Какими языками вы владеете?',
                    'en' => 'Which languages do you speak?',
                ],
            ],
            [
                'order' => 10,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => "Qayerda o‘qigansiz?",
                    'ru' => 'Где вы учились?',
                    'en' => 'Where did you study?',
                ],
            ],
            [
                'order' => 11,
                'question_type' => QuestionFile::INT->value,
                'translations' => [
                    'uz' => "Ish tajribalaringizni kiriting",
                    'ru' => 'Введите ваш опыт работы',
                    'en' => 'Enter your work experience',
                ],
            ],
        ];



        foreach ($optionalQuestions as $data) {
            $question = Question::create([
                'occupation_id' => null,
                'order' => $data['order'],
                'question_type' => $data['question_type'],
            ]);

            foreach ($data['translations'] as $lang => $text) {
                QuestionTranslation::create([
                    'question_id' => $question->id,
                    'lang_code' => $lang,
                    'text' => $text,
                ]);
            }
        }

        // 2️⃣ Occupation-specific questions (3 per occupation)
        $occupations = Occupation::all();

        $occupationQuestions = [
            'uz' => [
                ['Ish tajribangiz necha yil?', 'Oldingi ish joyingizda vazifangiz nima edi?', 'Ish vaqtidagi bosimni qanday boshqarasiz?'],
            ],
            'ru' => [
                ['Сколько лет у вас опыта?', 'Какая была ваша должность на прошлой работе?', 'Как вы справляетесь со стрессом на работе?'],
            ],
            'en' => [
                ['How many years of experience do you have?', 'What was your role at your previous job?', 'How do you handle work pressure?'],
            ],
        ];

        foreach ($occupations as $occupation) {
            foreach (range(1, 3) as $i) {
                $question = Question::create([
                    'occupation_id' => $occupation->id,
                    'order' => $i,
                   'question_type' => QuestionFile::INT->value,
                ]);

                foreach (['uz', 'ru', 'en'] as $lang) {
                    QuestionTranslation::create([
                        'question_id' => $question->id,
                        'lang_code' => $lang,
                        'text' => $occupationQuestions[$lang][0][$i - 1],
                    ]);
                }
            }
        }
    }
}
