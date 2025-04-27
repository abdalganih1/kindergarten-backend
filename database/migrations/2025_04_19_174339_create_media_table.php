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
        Schema::create('media', function (Blueprint $table) {
            $table->id('media_id');
            $table->string('file_path', 255);
            $table->enum('media_type', ['Image', 'Video']);
            $table->text('description')->nullable();
            $table->timestamp('upload_date')->useCurrent();
            // Foreign key to users table (uploader)
            $table->foreignId('uploader_id')->constrained('users', 'id')->onDelete('restrict')->onUpdate('cascade');
             // Foreign key to children table
            $table->foreignId('associated_child_id')->nullable()->constrained('children', 'child_id')->onDelete('set null')->onUpdate('cascade');
             // Foreign key to events table
            $table->foreignId('associated_event_id')->nullable()->constrained('events', 'event_id')->onDelete('set null')->onUpdate('cascade');
             // Foreign key to kindergarten_classes table
            $table->foreignId('associated_class_id')->nullable()->constrained('kindergarten_classes', 'class_id')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
