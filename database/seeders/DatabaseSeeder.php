<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // استدعِ الـ Seeders بالترتيب الصحيح
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            ParentSeeder::class,
            KindergartenClassSeeder::class,
            ChildSeeder::class,
            ParentChildSeeder::class, // Needs Parents and Children
            EventSeeder::class, // Needs Admins
            AnnouncementSeeder::class, // Needs Admins and Classes
            MediaSeeder::class, // Needs Users, Events, Classes, Children
            DailyMealSeeder::class, // Needs Classes
            EducationalResourceSeeder::class, // Needs Admins
            EventRegistrationSeeder::class, // Needs Events and Children
            HealthRecordSeeder::class, // Needs Children and Users
            WeeklyScheduleSeeder::class, // Needs Classes and Admins
            MessageSeeder::class, // Needs Users
            ObservationSeeder::class, // Needs Parents and Children
            // AttendanceSeeder::class, // Uncomment if you created and filled this
        ]);

        // يمكنك أيضاً استخدام Factories هنا لإنشاء بيانات أكثر عشوائية
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}