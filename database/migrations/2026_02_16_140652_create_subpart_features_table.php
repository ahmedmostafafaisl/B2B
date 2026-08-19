<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('subpart_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_part_id') ->constrained('sub_parts') ->cascadeOnDelete();
            $table->string('title'); $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['sub_part_id', 'is_active']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('subpart_features');
    }
};
