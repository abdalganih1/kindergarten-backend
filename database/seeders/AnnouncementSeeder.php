<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\KindergartenClass;
use App\Models\Announcement; // <-- Add this

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Announcement::truncate(); // Clear existing

        $admin = Admin::first(); // Assuming only one admin for now
        $class2 = KindergartenClass::where('min_age', 3)->first();

        if ($admin) {
            Announcement::create([
                'title' => 'أهلاً وسهلاً بالعام الدراسي الجديد',
                'content' => 'نرحب بجميع الأطفال وأولياء الأمور في بداية العام الدراسي. نتمنى لكم عاماً مليئاً بالتعلم والمرح.',
                'author_id' => $admin->admin_id,
                'target_class_id' => null, // For all classes
            ]);

            if ($class2) {
                 Announcement::create([
                    'title' => 'تذكير برحلة الحديقة',
                    'content' => 'نود تذكير أولياء أمور فصل (3-6) بالرحلة القادمة إلى الحديقة يوم الخميس. يرجى التأكد من إحضار موافقة الرحلة.',
                    'author_id' => $admin->admin_id,
                    'target_class_id' => $class2->class_id,
                 ]);
            }
        }
    }
}