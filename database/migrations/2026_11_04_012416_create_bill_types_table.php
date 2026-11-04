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
        Schema::create('bill_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. SPP, Uang Gedung, Tabungan
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('type', ['spp', 'custom', 'tabungan'])->default('spp');
            $table->boolean('is_discountable')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_types');
    }
};
