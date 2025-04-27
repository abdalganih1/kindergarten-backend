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
        Schema::create('daily_meals', function (Blueprint $table) {
            $table->id('meal_id');
            $table->date('meal_date');
            $table->enum('meal_type', ['Breakfast', 'Lunch', 'Snack']);
            $table->text('menu_description');
            // Foreign key to kindergarten_classes table
            $table->foreignId('class_id')->nullable()->constrained('kindergarten_classes', 'class_id')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps(); // Includes created_at

            // Add unique constraint
            $table->unique(['meal_date', 'meal_type', 'class_id'], 'meal_date_type_class_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_meals');
    }
};
