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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announcement_id');
            $table->string('title', 200);
            $table->text('content');
            $table->timestamp('publish_date')->useCurrent(); // Use current timestamp as default
             // Foreign key to admins table, referencing admin_id column
            $table->foreignId('author_id')->constrained('admins', 'admin_id')->onDelete('restrict')->onUpdate('cascade');
            // Foreign key to kindergarten_classes table, referencing class_id column
            $table->foreignId('target_class_id')->nullable()->constrained('kindergarten_classes', 'class_id')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
