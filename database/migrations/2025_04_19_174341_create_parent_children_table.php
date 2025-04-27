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
        Schema::create('parent_children', function (Blueprint $table) {
            // Foreign key to parents table, referencing parent_id column
            $table->foreignId('parent_id')->constrained('parents', 'parent_id')->onDelete('cascade')->onUpdate('cascade');
             // Foreign key to children table, referencing child_id column
            $table->foreignId('child_id')->constrained('children', 'child_id')->onDelete('cascade')->onUpdate('cascade');

            // Define composite primary key
            $table->primary(['parent_id', 'child_id']);

            // No timestamps needed for this simple pivot table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_children');
    }
};
