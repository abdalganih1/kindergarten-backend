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
        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            // Assumes 'users' table uses default 'id' as primary key
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('full_name', 150);
            $table->string('contact_email', 100)->unique()->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
