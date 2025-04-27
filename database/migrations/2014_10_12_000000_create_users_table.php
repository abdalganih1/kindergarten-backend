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
        Schema::create('users', function (Blueprint $table) {
            // $table->id('user_id'); // Use default 'id' for simplicity with Laravel or uncomment if needed
            $table->id();
            // $table->string('username', 100)->unique()->comment('Login username'); // Or keep email as primary login
            $table->string('name'); // Standard Laravel field, can store full name here
            $table->string('email')->unique()->comment('Login email'); // Use email for login
            $table->timestamp('email_verified_at')->nullable();
            // $table->string('password_hash', 255)->comment('Stores hashed password'); // Laravel handles this internally
            $table->string('password'); // Laravel handles hashing via Model cast
            $table->enum('role', ['Admin', 'Parent', 'Supervisor'])->comment('User role determines permissions');
            $table->boolean('is_active')->default(true)->comment('Whether the account is active');
            $table->rememberToken();
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
