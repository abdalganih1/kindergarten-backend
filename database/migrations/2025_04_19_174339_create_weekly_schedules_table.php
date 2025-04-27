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
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            // Foreign key to kindergarten_classes table
            $table->foreignId('class_id')->constrained('kindergarten_classes', 'class_id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity_description', 255);
             // Foreign key to admins table
            $table->foreignId('created_by_id')->constrained('admins', 'admin_id')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();

            // Add unique constraint
            $table->unique(['class_id', 'day_of_week', 'start_time'], 'class_day_time_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};
