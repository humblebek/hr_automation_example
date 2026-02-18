<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Occupation;
use App\Models\OccupationTranslation;
use App\Models\Department;

class OccupationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // 1️⃣ Production Department
            'Production Department' => [
                [
                    'translations' => [
                        'uz' => ['title' => 'Ishlab chiqarish muhandisi', 'description' => 'Ishlab chiqarish jarayonlarini nazorat qiladi va uskunalarni samarali ishlashini ta’minlaydi.'],
                        'ru' => ['title' => 'Инженер по производству', 'description' => 'Контролирует производственные процессы и обеспечивает эффективную работу оборудования.'],
                        'en' => ['title' => 'Production Engineer', 'description' => 'Oversees production processes and ensures equipment efficiency.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Liniya operatori', 'description' => 'Ishlab chiqarish liniyasida mashinalarni boshqaradi va sifatni kuzatadi.'],
                        'ru' => ['title' => 'Оператор линии', 'description' => 'Управляет машинами на производственной линии и следит за качеством.'],
                        'en' => ['title' => 'Line Operator', 'description' => 'Operates machines on the production line and monitors quality.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Qadoqlash xodimi', 'description' => 'Mahsulotlarni to‘g‘ri qadoqlaydi va yorliqlaydi.'],
                        'ru' => ['title' => 'Упаковщик', 'description' => 'Правильно упаковывает и маркирует продукцию.'],
                        'en' => ['title' => 'Packaging Worker', 'description' => 'Properly packages and labels products.'],
                    ],
                ],
            ],

            // 2️⃣ Quality Control Department
            'Quality Control Department' => [
                [
                    'translations' => [
                        'uz' => ['title' => 'Sifat nazoratchisi', 'description' => 'Mahsulot sifatini tekshiradi va talablar asosida baholaydi.'],
                        'ru' => ['title' => 'Контролёр качества', 'description' => 'Проверяет качество продукции и оценивает по стандартам.'],
                        'en' => ['title' => 'Quality Inspector', 'description' => 'Inspects product quality and evaluates against standards.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Laborant', 'description' => 'Namunalardan tahlil olib, sifat bo‘yicha hisobot tayyorlaydi.'],
                        'ru' => ['title' => 'Лаборант', 'description' => 'Проводит анализ образцов и готовит отчёты по качеству.'],
                        'en' => ['title' => 'Laboratory Technician', 'description' => 'Analyzes samples and prepares quality reports.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Sertifikatlash mutaxassisi', 'description' => 'Mahsulotlarning standartlarga mosligini tasdiqlaydi.'],
                        'ru' => ['title' => 'Специалист по сертификации', 'description' => 'Подтверждает соответствие продукции стандартам.'],
                        'en' => ['title' => 'Certification Specialist', 'description' => 'Verifies product compliance with standards.'],
                    ],
                ],
            ],

            // 3️⃣ Sales and Marketing Department
            'Sales and Marketing Department' => [
                [
                    'translations' => [
                        'uz' => ['title' => 'Marketing mutaxassisi', 'description' => 'Brendni targ‘ib qiladi va reklama kampaniyalarini boshqaradi.'],
                        'ru' => ['title' => 'Маркетолог', 'description' => 'Продвигает бренд и управляет рекламными кампаниями.'],
                        'en' => ['title' => 'Marketing Specialist', 'description' => 'Promotes the brand and manages advertising campaigns.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Savdo menejeri', 'description' => 'Mijozlar bilan ishlaydi va savdo hajmini oshiradi.'],
                        'ru' => ['title' => 'Менеджер по продажам', 'description' => 'Работает с клиентами и увеличивает объём продаж.'],
                        'en' => ['title' => 'Sales Manager', 'description' => 'Manages clients and increases sales volume.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Ijtimoiy tarmoqlar mutaxassisi', 'description' => 'Kompaniyaning onlayn obro‘sini rivojlantiradi.'],
                        'ru' => ['title' => 'Специалист по социальным сетям', 'description' => 'Развивает онлайн-репутацию компании.'],
                        'en' => ['title' => 'Social Media Specialist', 'description' => 'Develops company’s online reputation.'],
                    ],
                ],
            ],

            // 4️⃣ Logistics and Supply Department
            'Logistics and Supply Department' => [
                [
                    'translations' => [
                        'uz' => ['title' => 'Haydovchi', 'description' => 'Mahsulotlarni xavfsiz yetkazib beradi.'],
                        'ru' => ['title' => 'Водитель', 'description' => 'Обеспечивает безопасную доставку продукции.'],
                        'en' => ['title' => 'Driver', 'description' => 'Ensures safe product delivery.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Omborchi', 'description' => 'Ombor inventarini saqlaydi va boshqaradi.'],
                        'ru' => ['title' => 'Кладовщик', 'description' => 'Хранит и управляет складскими запасами.'],
                        'en' => ['title' => 'Warehouse Keeper', 'description' => 'Maintains and manages warehouse inventory.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Ta’minotchi', 'description' => 'Yetkazib beruvchilar bilan aloqalarni boshqaradi.'],
                        'ru' => ['title' => 'Специалист по снабжению', 'description' => 'Управляет связями с поставщиками.'],
                        'en' => ['title' => 'Supply Officer', 'description' => 'Manages supplier relations.'],
                    ],
                ],
            ],

            // 5️⃣ Human Resources Department
            'Human Resources Department' => [
                [
                    'translations' => [
                        'uz' => ['title' => 'HR menejeri', 'description' => 'Kadr siyosatini boshqaradi va xodimlar bilan ishlaydi.'],
                        'ru' => ['title' => 'HR менеджер', 'description' => 'Управляет кадровой политикой и персоналом.'],
                        'en' => ['title' => 'HR Manager', 'description' => 'Manages HR policies and staff relations.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Kadrlar bo‘yicha mutaxassis', 'description' => 'Yangi xodimlarni tanlaydi va hujjatlarni yuritadi.'],
                        'ru' => ['title' => 'Специалист по кадрам', 'description' => 'Отбирает новых сотрудников и ведёт документацию.'],
                        'en' => ['title' => 'HR Specialist', 'description' => 'Recruits new staff and manages documentation.'],
                    ],
                ],
                [
                    'translations' => [
                        'uz' => ['title' => 'Trening koordinatori', 'description' => 'Xodimlar uchun o‘quv dasturlarini tashkil qiladi.'],
                        'ru' => ['title' => 'Координатор обучения', 'description' => 'Организует программы обучения для сотрудников.'],
                        'en' => ['title' => 'Training Coordinator', 'description' => 'Organizes training programs for employees.'],
                    ],
                ],
            ],
        ];

        foreach ($data as $departmentNameEn => $occupations) {
            $department = Department::whereHas('departmentTranslations', function ($q) use ($departmentNameEn) {
                $q->where('lang_code', 'en')->where('name', $departmentNameEn);
            })->first();

            if ($department) {
                foreach ($occupations as $occData) {
                    $occupation = Occupation::create([
                        'department_id' => $department->id,
                        'status' => \App\Enum\Status::ACTIVE,
                        'photo' => 'occupation/dinay.jpeg',
                    ]);

                    foreach ($occData['translations'] as $lang => $fields) {
                        OccupationTranslation::create([
                            'occupation_id' => $occupation->id,
                            'lang_code' => $lang,
                            'title' => $fields['title'],
                            'description' => $fields['description'],
                        ]);
                    }
                }
            }
        }
    }
}
