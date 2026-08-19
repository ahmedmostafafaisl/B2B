<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('sub_service_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sub_service_id')
                ->constrained('sub_services')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('image')->nullable(); // image storage path (S3 or local)
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['sub_service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_service_features');
    }
};
