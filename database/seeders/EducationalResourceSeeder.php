<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\EducationalResource; // <-- Add this

class EducationalResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EducationalResource::truncate(); // Clear existing
        $admin = Admin::first();

        if($admin) {
            EducationalResource::create([
                'title' => 'أغنية الحروف الأبجدية',
                'description' => 'فيديو تعليمي ممتع لتعلم الحروف العربية',
                'resource_type' => 'Video',
                'url_or_path' => 'https://youtube.com/example_arabic_abc',
                'target_age_min' => 3,
                'target_age_max' => 6,
                'subject' => 'اللغة العربية',
                'added_by_id' => $admin->admin_id,
            ]);
             EducationalResource::create([
                'title' => 'تعلم الأشكال الهندسية',
                'description' => 'نشاط تفاعلي لتعريف الأطفال بالأشكال الأساسية',
                'resource_type' => 'Link',
                'url_or_path' => 'http://example.com/shapes_game',
                'target_age_min' => 2,
                'target_age_max' => 5,
                'subject' => 'الرياضيات',
                'added_by_id' => $admin->admin_id,
            ]);
        }
    }
}