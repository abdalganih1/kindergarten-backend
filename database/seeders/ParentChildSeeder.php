<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParentModel; // Use correct name
use App\Models\Child;
use Illuminate\Support\Facades\DB; // <-- Add for direct pivot insertion

class ParentChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: Clean up pivot table
        DB::table('parent_children')->query()->delete(); // Truncate is safe for pivot tables

        $parent1 = ParentModel::where('contact_email', 'parent1@example.com')->first();
        $parent2 = ParentModel::where('contact_email', 'parent2@example.com')->first();

        $child1 = Child::where('first_name', 'أحمد')->first();
        $child2 = Child::where('first_name', 'فاطمة')->first();
        $child3 = Child::where('first_name', 'زينب')->first();

        // Use attach() method on the relationship
        if ($parent1 && $child1) {
            $parent1->children()->attach($child1->child_id);
        }
        if ($parent1 && $child2) {
            $parent1->children()->attach($child2->child_id);
        }
        if ($parent2 && $child3) {
            $parent2->children()->attach($child3->child_id);
        }
    }
}