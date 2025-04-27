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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id('registration_id');
            // Foreign key to events table
            $table->foreignId('event_id')->constrained('events', 'event_id')->onDelete('cascade')->onUpdate('cascade');
             // Foreign key to children table
            $table->foreignId('child_id')->constrained('children', 'child_id')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('registration_date')->useCurrent();
            $table->boolean('parent_consent')->default(false);
            $table->timestamps();

            // Add unique constraint
             $table->unique(['event_id', 'child_id'], 'event_child_registration_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
