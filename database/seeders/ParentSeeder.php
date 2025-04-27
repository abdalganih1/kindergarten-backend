<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ParentModel; // Use ParentModel if renamed

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentUser1 = User::where('email', 'parent1@example.com')->first();
        $parentUser2 = User::where('email', 'parent2@example.com')->first();

        if ($parentUser1) {
             // Ensure no duplicates
            ParentModel::where('user_id', $parentUser1->id)->delete();
            ParentModel::create([
                'user_id' => $parentUser1->id,
                'full_name' => $parentUser1->name,
                'contact_email' => $parentUser1->email,
                'contact_phone' => '987-654-3210',
                'address' => '123 Main St, Anytown',
            ]);
        }

        if ($parentUser2) {
             // Ensure no duplicates
            ParentModel::where('user_id', $parentUser2->id)->delete();
            ParentModel::create([
                'user_id' => $parentUser2->id,
                'full_name' => $parentUser2->name,
                'contact_email' => $parentUser2->email,
                'contact_phone' => '555-555-5555',
                'address' => '456 Oak Ave, Anytown',
            ]);
        }
    }
}