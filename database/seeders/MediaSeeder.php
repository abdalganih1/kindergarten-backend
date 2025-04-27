<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\KindergartenClass;
use App\Models\Event;
use App\Models\Media; // <-- Add this

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Media::query()->delete(); // Clear existing

        $uploader = User::where('role', 'Admin')->first(); // Admin user
        $class2 = KindergartenClass::where('min_age', 3)->first();
        $zooEvent = Event::where('event_name', 'رحلة إلى حديقة الحيوان')->first();

        if ($uploader) {
            if ($class2) {
                 Media::create([
                    'file_path' => 'uploads/images/sample_art_craft.jpg', // Use sample paths
                    'media_type' => 'Image',
                    'description' => 'الأطفال يستمتعون بوقت الفنون والحرف اليدوية',
                    'uploader_id' => $uploader->id,
                    'associated_class_id' => $class2->class_id,
                    'associated_child_id' => null,
                    'associated_event_id' => null,
                 ]);
            }

             Media::create([
                    'file_path' => 'uploads/videos/sample_playground.mp4',
                    'media_type' => 'Video',
                    'description' => 'وقت اللعب في الساحة الخارجية',
                    'uploader_id' => $uploader->id,
                    'associated_class_id' => null,
                    'associated_child_id' => null,
                    'associated_event_id' => null,
             ]);

             if ($zooEvent) {
                  Media::create([
                    'file_path' => 'uploads/images/sample_zoo_trip.jpg',
                    'media_type' => 'Image',
                    'description' => 'صورة جماعية من رحلة حديقة الحيوان',
                    'uploader_id' => $uploader->id,
                    'associated_class_id' => null,
                    'associated_child_id' => null,
                    'associated_event_id' => $zooEvent->event_id,
                 ]);
             }
        }
    }
}