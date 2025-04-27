<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Child;
use App\Models\EventRegistration; // <-- Add this

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventRegistration::truncate(); // Clear existing

        $zooEvent = Event::where('event_name', 'رحلة إلى حديقة الحيوان')->first();
        $child2 = Child::where('first_name', 'فاطمة')->first();
        $child3 = Child::where('first_name', 'زينب')->first();

        if ($zooEvent && $child2) {
             EventRegistration::create([
                'event_id' => $zooEvent->event_id,
                'child_id' => $child2->child_id,
                'parent_consent' => true,
             ]);
        }
         if ($zooEvent && $child3) {
             EventRegistration::create([
                'event_id' => $zooEvent->event_id,
                'child_id' => $child3->child_id,
                'parent_consent' => true,
             ]);
        }
    }
}