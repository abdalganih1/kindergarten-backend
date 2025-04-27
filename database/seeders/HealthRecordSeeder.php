<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Child;
use App\Models\User;
use App\Models\HealthRecord; // <-- Add this

class HealthRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HealthRecord::truncate(); // Clear existing

        $child1 = Child::where('first_name', 'أحمد')->first();
        $child2 = Child::where('first_name', 'فاطمة')->first();
        $child3 = Child::where('first_name', 'زينب')->first();

        $adminUser = User::where('role', 'Admin')->first();
        $parentUser2 = User::where('email', 'parent2@example.com')->first(); // Assuming Parent 2 entered one

        if ($child1 && $adminUser) {
            HealthRecord::create([
                'child_id' => $child1->child_id,
                'record_type' => 'Vaccination',
                'record_date' => '2023-05-15',
                'details' => 'لقاح الحصبة والنكاف والحصبة الألمانية (MMR) - الجرعة الأولى',
                'next_due_date' => '2027-05-15',
                'entered_by_id' => $adminUser->id,
            ]);
        }
        if ($child2 && $parentUser2) { // Assuming parent 2 is user_id 3 who entered for child 2
            HealthRecord::create([
                'child_id' => $child2->child_id,
                'record_type' => 'Checkup',
                'record_date' => '2024-01-20',
                'details' => 'فحص طبي دوري - كل شيء طبيعي. تم التوصية بفحص نظر.',
                'next_due_date' => null,
                'entered_by_id' => $parentUser2->id, // Or use ID 3 directly if known
            ]);
        }
        if ($child3 && $adminUser) {
            HealthRecord::create([
                'child_id' => $child3->child_id,
                'record_type' => 'MedicationAdministered',
                'record_date' => '2024-03-09',
                'details' => 'تم إعطاء دواء للحساسية (مضاد هيستامين) بناءً على طلب ولي الأمر.',
                'next_due_date' => null,
                'entered_by_id' => $adminUser->id,
            ]);
        }
    }
}