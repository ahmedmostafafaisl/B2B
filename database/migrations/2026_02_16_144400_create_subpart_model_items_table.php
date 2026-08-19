<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

 public function up(): void
{
    Schema::create('subpart_model_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('subpart_model_section_id')
            ->constrained('subpart_model_sections')
            ->cascadeOnDelete();

        $table->string('text');
        $table->unsignedInteger('sort_order')->default(0);

        $table->timestamps();

        $table->index(['subpart_model_section_id']);
    });
}



    public function down(): void
    {
        Schema::dropIfExists('subpart_model_items');
    }
};
