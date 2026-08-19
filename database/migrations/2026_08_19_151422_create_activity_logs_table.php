<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Generic, model-agnostic audit log. Any model can log activity against
     * itself by using the HasActivityLogs trait — this table replaces the
     * Contact-only `contact_logs` table as the write target going forward.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Polymorphic target: which model + row this log entry belongs to.
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');
            $table->index(['loggable_type', 'loggable_id'], 'activity_logs_loggable_index');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action')->nullable()->index();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
