<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentTranslation;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'translations' => [
                    'uz' => 'Ishlab chiqarish bo‘limi',
                    'ru' => 'Производственный отдел',
                    'en' => 'Production Department',
                ],
            ],
            [
                'translations' => [
                    'uz' => 'Sifat nazorati bo‘limi',
                    'ru' => 'Отдел контроля качества',
                    'en' => 'Quality Control Department',
                ],
            ],
            [
                'translations' => [
                    'uz' => 'Savdo va marketing bo‘limi',
                    'ru' => 'Отдел продаж и маркетинга',
                    'en' => 'Sales and Marketing Department',
                ],
            ],
            [
                'translations' => [
                    'uz' => 'Logistika va ta’minot bo‘limi',
                    'ru' => 'Отдел логистики и снабжения',
                    'en' => 'Logistics and Supply Department',
                ],
            ],
            [
                'translations' => [
                    'uz' => 'Kadrlar bo‘limi',
                    'ru' => 'Отдел кадров',
                    'en' => 'Human Resources Department',
                ],
            ],
        ];

        foreach ($departments as $departmentData) {
            $department = Department::create([
                'status' => \App\Enum\Status::ACTIVE,
            ]);

            foreach ($departmentData['translations'] as $lang => $name) {
                DepartmentTranslation::create([
                    'department_id' => $department->id,
                    'lang_code' => $lang,
                    'name' => $name,
                ]);
            }
        }
    }
}
