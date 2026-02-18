<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Enum\Gender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure you already have region_id = 1, district_id = 1, status_id = 1 in DB
        // or adjust these IDs accordingly.

        $regions = DB::table('regions')->pluck('id')->toArray();
        $districts = DB::table('districts')->pluck('id')->toArray();
        $statuses = DB::table('statuses')->pluck('id')->toArray();
        $sources = DB::table('sources')->pluck('id')->toArray();

        if (empty($regions) || empty($districts) || empty($statuses)) {
            $this->command->warn('⚠️ Please seed regions, districts, and statuses first!');
            return;
        }

        $names = [
            'Azizbek Jo‘rayev', 'Mohira Karimova', 'Jasur Akhmedov', 'Malika Nuralieva',
            'Sherzod Rasulov', 'Nigora G‘aniyeva', 'Rustam Alimov', 'Gulnoza Hamidova',
            'Bekzod Komilov', 'Dilnoza Tursunova'
        ];

        foreach ($names as $index => $name) {
            $isMale = $index % 2 === 0;
            $gender = $isMale ? Gender::MALE->value : Gender::FEMALE->value;

            Application::create([
                'telegram_id'   => 'tg_' . Str::random(10),
                'full_name'     => $name,
                'phone'         => '+99899' . rand(1000000, 9999999),
                'image'         => 'https://randomuser.me/api/portraits/' . ($isMale ? 'men' : 'women') . '/' . rand(1, 90) . '.jpg',
                'birth_date'    => Carbon::now()->subYears(rand(18, 40))->format('Y-m-d'),
                'sex'           => $gender,
                'region_id'     => $regions[array_rand($regions)],
                'district_id'   => $districts[array_rand($districts)],
                'nationality'   => rand(100, 700), // depends on your Natinalities enum
                'selected_lang' => \App\Enum\Lang::UZ,
                'status_id'     => $statuses[array_rand($statuses)],
                'source_id'     => $sources[array_rand($sources)],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->command->info('✅ 10 test applicants created successfully!');
    }
}
