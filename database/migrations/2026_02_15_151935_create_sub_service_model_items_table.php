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
      Schema::create('sub_service_model_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sub_service_model_section_id')
        ->constrained('sub_service_model_sections')
        ->cascadeOnDelete();

    $table->string('text');
    $table->unsignedInteger('sort_order')->default(0);

    $table->timestamps();

    $table->index(['sub_service_model_section_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_service_model_items');
    }
};
