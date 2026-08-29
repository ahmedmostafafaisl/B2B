<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per user per assigned event. A single "create" request with
     * multiple user_ids fans out into one row per user, all sharing the
     * same event_type/status/note/schedule.
     */
    public function up(): void
    {
        Schema::create('user_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('event_type_id')
                ->constrained('event_types')
                ->cascadeOnDelete();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('note')->nullable();

            // Date-range mode (e.g. a vacation from start_at to end_at).
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();

            // Recurring weekly-schedule mode (e.g. a work shift on certain
            // days between start_time and end_time).
            $table->json('days_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['start_at', 'end_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_events');
    }
};
