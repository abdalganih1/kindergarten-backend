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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendance_id');
            // Foreign key to children table
            $table->foreignId('child_id')->constrained('children', 'child_id')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('notes')->nullable();
             // Foreign key to users table (who recorded it) - Nullable in case it's automated
            $table->foreignId('recorded_by_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();

             // Ensure a child has only one record per day
            $table->unique(['child_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
