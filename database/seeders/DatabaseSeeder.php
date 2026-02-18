<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'HR Auto',
            'email' => 'hrauto@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('hrauto123')
        ]);

        $this->call([
            TerritorySeeder::class,
            DepartmentSeeder::class,
            OccupationSeeder::class,
            QuestionSeeder::class,
            StatusSeeder::class,
        ]);

    }
}
