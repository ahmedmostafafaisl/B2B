<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_service_feature_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sub_service_feature_id')
                ->constrained('sub_service_features')
                ->cascadeOnDelete();

            $table->string('name'); // e.g. Standard Features, Available Options
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['sub_service_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_service_feature_types');
    }
};
