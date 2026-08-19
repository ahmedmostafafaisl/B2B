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
    Schema::create('subpart_model_sections', function (Blueprint $table) {
        $table->id();

        $table->foreignId('subpart_model_id')
            ->constrained('subpart_models')
            ->cascadeOnDelete();

        $table->string('title'); // e.g. Operation Specifics
        $table->unsignedInteger('sort_order')->default(0);

        $table->timestamps();

        $table->index(['subpart_model_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subpart_model_sections');
    }
};
