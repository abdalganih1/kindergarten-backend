<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KindergartenClass;
use App\Models\Child; // <-- Add this

class ChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: Clean up before seeding
        Child::query()->delete(); // Be careful with truncate and FKs, delete might be safer

        $class1 = KindergartenClass::where('min_age', 0)->first();
        $class2 = KindergartenClass::where('min_age', 3)->first();

        if ($class1) {
            Child::create([
                'first_name' => 'أحمد',
                'last_name' => 'علي',
                'date_of_birth' => '2022-05-15',
                'gender' => 'Male',
                'enrollment_date' => '2024-01-10',
                'class_id' => $class1->class_id, // Use the actual class_id
                'allergies' => 'حساسية الفول السوداني',
                'medical_notes' => 'لا يوجد',
            ]);
        }

        if ($class2) {
            Child::create([
                'first_name' => 'فاطمة',
                'last_name' => 'محمد',
                'date_of_birth' => '2020-11-20',
                'gender' => 'Female',
                'enrollment_date' => '2024-01-10',
                'class_id' => $class2->class_id,
                'allergies' => null,
                'medical_notes' => 'تحتاج نظارة للقراءة',
            ]);

            Child::create([
                'first_name' => 'زينب',
                'last_name' => 'حسن',
                'date_of_birth' => '2021-08-01',
                'gender' => 'Female',
                'enrollment_date' => '2024-02-01',
                'class_id' => $class2->class_id,
                'allergies' => 'حساسية اللاكتوز',
                'medical_notes' => null,
            ]);
        }
    }
}