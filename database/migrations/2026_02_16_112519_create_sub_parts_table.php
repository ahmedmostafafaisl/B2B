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
        Schema::create('sub_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->nullable()->constrained('parts')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('sub_parts')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('primary_image')->nullable();
            $table->string('banner')->nullable();
            $table->string('image_365')->nullable();
            $table->text('description_365')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_parts');
    }
};
