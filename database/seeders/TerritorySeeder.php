<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/sql/regions.sql');

        if (!file_exists($path)) {
            $this->command->error("❌ SQL file not found at: {$path}");
            return;
        }

        DB::beginTransaction();
        try {
            DB::unprepared(file_get_contents($path));
            DB::commit();
            $this->command->info('✅ Regions and Districts seeded successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('❌ Error seeding data: ' . $e->getMessage());
        }
    }
}
