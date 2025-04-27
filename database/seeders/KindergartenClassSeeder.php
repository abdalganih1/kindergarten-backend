<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KindergartenClass; // <-- Add this

class KindergartenClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Optional: Clean up before seeding
         KindergartenClass::query()->delete(); // Use delete() which respects FK constraints

        KindergartenClass::create([
            'class_name' => 'الأطفال الصغار (أقل من 3)',
            'description' => 'مجموعة الأطفال تحت سن الثالثة',
            'min_age' => 0,
            'max_age' => 2,
        ]);

        KindergartenClass::create([
            'class_name' => 'مرحلة ما قبل المدرسة (3-6)',
            'description' => 'مجموعة الأطفال من سن 3 إلى 6 سنوات',
            'min_age' => 3,
            'max_age' => 6,
        ]);
    }
}