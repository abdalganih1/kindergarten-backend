<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // <-- Add this
use App\Models\User; // <-- Add this

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure no duplicates if run multiple times without fresh migration
        User::whereIn('email', ['admin@kindergarten.com', 'parent1@example.com', 'parent2@example.com'])->delete();

        User::create([
            'name' => 'مدير النظام', // Or 'Admin User'
            'email' => 'admin@kindergarten.com',
            'password' => Hash::make('password'), // استبدل بكلمة مرور قوية
            'role' => 'Admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'ولي أمر ١', // Or 'Parent One'
            'email' => 'parent1@example.com',
            'password' => Hash::make('password'), // استبدل بكلمة مرور قوية
            'role' => 'Parent',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'ولي أمر ٢', // Or 'Parent Two'
            'email' => 'parent2@example.com',
            'password' => Hash::make('password'), // استبدل بكلمة مرور قوية
            'role' => 'Parent',
            'is_active' => true,
        ]);

        // Add Supervisor user if needed
        // User::create([
        //     'name' => 'مشرف ١',
        //     'email' => 'supervisor1@kindergarten.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'Supervisor',
        //     'is_active' => true,
        // ]);
    }
}