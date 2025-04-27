<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KindergartenClass;
use App\Models\DailyMeal; // <-- Add this

class DailyMealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DailyMeal::query()->delete(); // Clear existing

         $class2 = KindergartenClass::where('min_age', 3)->first();

         DailyMeal::create([
            'meal_date' => '2024-03-10',
            'meal_type' => 'Lunch',
            'menu_description' => 'دجاج مشوي مع أرز وخضروات سوتيه',
            'class_id' => null, // General
         ]);
         DailyMeal::create([
            'meal_date' => '2024-03-10',
            'meal_type' => 'Snack',
            'menu_description' => 'فواكه طازجة (تفاح وموز)',
            'class_id' => null, // General
         ]);
         if ($class2) {
              DailyMeal::create([
                'meal_date' => '2024-03-11',
                'meal_type' => 'Lunch',
                'menu_description' => 'معكرونة بالصلصة الحمراء وسلطة خضراء',
                'class_id' => $class2->class_id,
             ]);
         }
    }
}