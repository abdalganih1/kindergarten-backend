<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supervisor_classes', function (Blueprint $table) {
            // استخدام foreignId للمفاتيح الأجنبية القياسية
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to users table
            $table->foreignId('class_id')->constrained('kindergarten_classes', 'class_id')->onDelete('cascade'); // FK to kindergarten_classes table

            // تحديد المفتاح الأساسي المركب
            $table->primary(['user_id', 'class_id']);

            // لا نحتاج timestamps هنا عادةً
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_classes');
    }
};
