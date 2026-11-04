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
        Schema::create('bill_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('student_category', ['all', 'reguler', 'beasiswa_50', 'beasiswa_100'])->default('all');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_assignments');
    }
};
