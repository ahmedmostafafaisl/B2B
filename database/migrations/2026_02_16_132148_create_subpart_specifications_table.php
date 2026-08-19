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
        Schema::create('subpart_specifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sub_part_id')
            ->constrained('sub_parts')
            ->cascadeOnDelete();
        $table->string('type', 100);
        $table->string('title', 255);
        $table->text('description')->nullable();
        $table->timestamps();
        $table->index(['sub_part_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subpart_specifications');
    }
};
