<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Event; // <-- Add this

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::query()->delete(); // Clear existing

        $admin = Admin::first();

        if ($admin) {
            Event::create([
                'event_name' => 'رحلة إلى حديقة الحيوان',
                'description' => 'زيارة تعليمية وترفيهية لحديقة الحيوان للتعرف على الحيوانات.',
                'event_date' => '2024-04-15 09:00:00',
                'location' => 'حديقة الحيوان المحلية',
                'requires_registration' => true,
                'registration_deadline' => '2024-04-01 17:00:00',
                'created_by_id' => $admin->admin_id,
            ]);

             Event::create([
                'event_name' => 'يوم الأنشطة الرياضية',
                'description' => 'يوم مفتوح للأنشطة الرياضية والألعاب في ساحة الروضة.',
                'event_date' => '2024-05-10 10:00:00',
                'location' => 'ساحة الروضة',
                'requires_registration' => false,
                'registration_deadline' => null,
                'created_by_id' => $admin->admin_id,
            ]);
        }
    }
}