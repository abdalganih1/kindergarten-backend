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
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            // Foreign key to users table (sender)
            $table->foreignId('sender_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
             // Foreign key to users table (recipient)
            $table->foreignId('recipient_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
