<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Message; // <-- Add this

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Message::truncate(); // Clear existing

        $adminUser = User::where('role', 'Admin')->first();
        $parentUser1 = User::where('email', 'parent1@example.com')->first();

        if($adminUser && $parentUser1) {
            Message::create([
                'sender_id' => $adminUser->id,
                'recipient_id' => $parentUser1->id,
                'subject' => 'بخصوص رحلة الحديقة',
                'body' => 'السيدة/السيد ولي أمر ١، يرجى التأكد من إرسال موافقة الرحلة لابنتكم فاطمة قبل يوم الخميس. شكراً لتعاونكم.',
                'read_at' => null, // Initially unread
            ]);
        }
    }
}