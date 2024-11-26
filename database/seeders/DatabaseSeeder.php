<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Washroom;
use App\Models\Toilet;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::create([
                'name' => 'Sarwar',
                'employee_id' => '100002',
        ]);

        User::create([
            'name' => 'Banna',
            'employee_id' => '100003'
        ]);
        
        User::create([
            'name' => 'Monaem',
            'employee_id' => '100004',
        ]);

        // Create Male Washroom
        $maleWashroom = Washroom::create([
            'name' => '11th Floor Male Washroom',
            'floor' => '11',
            'type' => 'male',
            'is_operational' => true,
        ]);

        // Create toilets for male washroom
        for ($i = 1; $i <= 2; $i++) {
            Toilet::create([
                'washroom_id' => $maleWashroom->id,
                'number' => "M{$i}",
                'is_occupied' => false,
                'is_operational' => true,
            ]);
        }

        // Create Female Washroom
        $femaleWashroom = Washroom::create([
            'name' => '11th Floor Female Washroom',
            'floor' => '11',
            'type' => 'female',
            'is_operational' => true,
        ]);

        // Create toilet for female washroom
        Toilet::create([
            'washroom_id' => $femaleWashroom->id,
            'number' => 'F1',
            'is_occupied' => false,
            'is_operational' => true,
        ]);

        // Create Unisex/Accessible Washroom
        $unisexWashroom = Washroom::create([
            'name' => '11th Floor Accessible Washroom',
            'floor' => '11',
            'type' => 'unisex',
            'is_operational' => true,
        ]);

        // Create toilet for unisex washroom
        Toilet::create([
            'washroom_id' => $unisexWashroom->id,
            'number' => 'U1',
            'is_occupied' => false,
            'is_operational' => true,
        ]);
    }
}
