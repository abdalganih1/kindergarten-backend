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
        Schema::create('observations', function (Blueprint $table) {
            $table->id('observation_id');
            // Foreign key to parents table
            $table->foreignId('parent_id')->constrained('parents', 'parent_id')->onDelete('cascade')->onUpdate('cascade');
             // Foreign key to children table
            $table->foreignId('child_id')->nullable()->constrained('children', 'child_id')->onDelete('set null')->onUpdate('cascade');
            $table->text('observation_text');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
