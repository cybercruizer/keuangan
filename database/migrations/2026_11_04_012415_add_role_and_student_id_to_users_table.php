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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->enum('role', ['admin', 'teller', 'kepsek', 'murid'])->default('admin')->after('password');
            $table->foreignId('student_id')->nullable()->after('role')->constrained('students')->nullOnDelete();
            $table->string('phone')->nullable()->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role', 'student_id', 'phone']);
        });
    }
};
