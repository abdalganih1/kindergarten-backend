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
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('event_name', 200);
            $table->text('description')->nullable();
            $table->dateTime('event_date');
            $table->string('location', 255)->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->dateTime('registration_deadline')->nullable();
             // Foreign key to admins table
            $table->foreignId('created_by_id')->constrained('admins', 'admin_id')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
