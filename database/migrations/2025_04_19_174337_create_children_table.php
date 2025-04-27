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
        Schema::create('children', function (Blueprint $table) {
            $table->id('child_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('enrollment_date');
            // Foreign key to kindergarten_classes table, referencing class_id column
            $table->foreignId('class_id')->nullable()->constrained('kindergarten_classes', 'class_id')->onDelete('set null')->onUpdate('cascade');
            $table->text('allergies')->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->timestamps(); // Includes created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
