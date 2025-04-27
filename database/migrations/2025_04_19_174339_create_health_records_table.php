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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id('record_id');
             // Foreign key to children table
            $table->foreignId('child_id')->constrained('children', 'child_id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('record_type', ['Vaccination', 'Checkup', 'Illness', 'MedicationAdministered']);
            $table->date('record_date');
            $table->text('details');
            $table->date('next_due_date')->nullable();
            $table->string('document_path', 255)->nullable();
            // Foreign key to users table (who entered it)
            $table->foreignId('entered_by_id')->constrained('users', 'id')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps(); // created_at represents entered_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
