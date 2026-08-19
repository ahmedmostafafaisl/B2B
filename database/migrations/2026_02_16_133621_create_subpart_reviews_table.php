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
        Schema::create('subpart_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_part_id')
                ->constrained('sub_parts')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rate'); // 1..5
            $table->string('reviewer_name', 150);
            $table->string('subject', 255);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subpart_reviews');
    }
};
