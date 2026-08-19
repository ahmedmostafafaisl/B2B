<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_service_feature_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sub_service_feature_type_id')
                ->constrained('sub_service_feature_types')
                ->cascadeOnDelete();

            $table->string('text'); // bullet item
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['sub_service_feature_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_service_feature_items');
    }
};
