<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove email unique index if it exists
        try {
            DB::statement('ALTER TABLE contacts DROP INDEX contacts_email_unique');
        } catch (\Throwable $e) {
            // Ignore
        }

        // Update status enum
        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM(
                'new',
                'in_progress',
                'contacted',
                'offer_price',
                'completed',
                'closed'
            ) NOT NULL DEFAULT 'new'
        ");

        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'offer_price')) {
                $table->decimal('offer_price', 10, 2)->nullable()->after('status');
            }

            if (!Schema::hasColumn('contacts', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('offer_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'offer_price')) {
                $table->dropColumn('offer_price');
            }

            if (Schema::hasColumn('contacts', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM(
                'new',
                'in_progress',
                'contacted',
                'closed'
            ) NOT NULL DEFAULT 'new'
        ");

        DB::statement('ALTER TABLE contacts ADD UNIQUE INDEX contacts_email_unique (email)');
    }
};
