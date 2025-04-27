<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KindergartenClass;
use App\Models\Admin;
use App\Models\WeeklySchedule; // <-- Add this

class WeeklyScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WeeklySchedule::truncate(); // Clear existing

        $class2 = KindergartenClass::where('min_age', 3)->first();
        $admin = Admin::first();

        if($class2 && $admin) {
             WeeklySchedule::create([
                'class_id' => $class2->class_id,
                'day_of_week' => 'Monday',
                'start_time' => '09:00:00',
                'end_time' => '09:30:00',
                'activity_description' => 'حلقة الصباح والترحيب',
                'created_by_id' => $admin->admin_id,
             ]);
             WeeklySchedule::create([
                'class_id' => $class2->class_id,
                'day_of_week' => 'Monday',
                'start_time' => '09:30:00',
                'end_time' => '10:30:00',
                'activity_description' => 'أنشطة تعليمية (حروف وأرقام)',
                'created_by_id' => $admin->admin_id,
             ]);
             WeeklySchedule::create([
                'class_id' => $class2->class_id,
                'day_of_week' => 'Monday',
                'start_time' => '10:30:00',
                'end_time' => '11:00:00',
                'activity_description' => 'وقت الوجبة الخفيفة',
                'created_by_id' => $admin->admin_id,
             ]);
             WeeklySchedule::create([
                'class_id' => $class2->class_id,
                'day_of_week' => 'Wednesday',
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'activity_description' => 'فنون وحرف يدوية',
                'created_by_id' => $admin->admin_id,
             ]);
        }
    }
}