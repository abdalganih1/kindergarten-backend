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
        Schema::create('educational_resources', function (Blueprint $table) {
            $table->id('resource_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('resource_type', ['Video', 'Article', 'Game', 'Link'])->default('Video');
            $table->string('url_or_path', 255);
            $table->unsignedTinyInteger('target_age_min')->nullable();
            $table->unsignedTinyInteger('target_age_max')->nullable();
            $table->string('subject', 100)->nullable();
             // Foreign key to admins table
            $table->foreignId('added_by_id')->constrained('admins', 'admin_id')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps(); // created_at represents added_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_resources');
    }
};
