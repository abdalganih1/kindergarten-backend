<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParentModel; // Use correct name
use App\Models\Child;
use App\Models\Observation; // <-- Add this

class ObservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Observation::truncate(); // Clear existing

         $parent2 = ParentModel::whereHas('user', function($q){
                $q->where('email', 'parent2@example.com');
         })->first();
         $child3 = Child::where('first_name', 'زينب')->first();

         if($parent2 && $child3) {
            Observation::create([
                'parent_id' => $parent2->parent_id,
                'child_id' => $child3->child_id,
                'observation_text' => 'لاحظت أن زينب متحمسة جداً لنشاط الفنون والحرف اليدوية الأخير.',
            ]);
         }
    }
}