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
        Schema::create('kindergarten_classes', function (Blueprint $table) {
            $table->id('class_id');
            $table->string('class_name', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kindergarten_classes');
    }
};
