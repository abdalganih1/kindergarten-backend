<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin; // <-- Add this

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@kindergarten.com')->first();

        if ($adminUser) {
            // Ensure no duplicates
            Admin::where('user_id', $adminUser->id)->delete();

            Admin::create([
                'user_id' => $adminUser->id,
                'full_name' => $adminUser->name, // Use name from user table
                'contact_email' => $adminUser->email,
                'contact_phone' => '123-456-7890',
            ]);
        }
    }
}