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
        Schema::create('subpart_feature_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subpart_feature_type_id') ->constrained('subpart_feature_types') ->cascadeOnDelete();
            $table->string('text'); // bullet item
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['subpart_feature_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subpart_feature_items');
    }
};
