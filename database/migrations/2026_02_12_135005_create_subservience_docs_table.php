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
        Schema::create('subservience_docs', function (Blueprint $table) {
            $table->id();
               $table->foreignId('sub_service_id')
                ->constrained('sub_services')
                ->cascadeOnDelete();
                   $table->string('title', 255);
            $table->string('file_path'); // S3 key/path
            $table->string('file_original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
             $table->index(['sub_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subservience_docs');
    }
};
