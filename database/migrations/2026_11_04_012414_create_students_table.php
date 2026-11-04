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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->foreignId('class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();
            $table->enum('category', ['reguler', 'beasiswa_50', 'beasiswa_100'])->default('reguler');
            $table->string('phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'mutasi', 'keluar'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
