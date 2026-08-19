<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Clean up orphaned references before adding FK constraints ──────
        // contacts.subject_id / key_id had no FK before, so it's possible
        // some rows point at a subject/key that no longer exists.
        DB::statement('
            UPDATE contacts
            LEFT JOIN subjects ON subjects.id = contacts.subject_id
            SET contacts.subject_id = NULL
            WHERE contacts.subject_id IS NOT NULL
              AND subjects.id IS NULL
        ');

        // `keys` is a reserved word in MySQL, so it must be backtick-quoted.
        DB::statement('
            UPDATE contacts
            LEFT JOIN `keys` ON `keys`.id = contacts.key_id
            SET contacts.key_id = NULL
            WHERE contacts.key_id IS NOT NULL
              AND `keys`.id IS NULL
        ');

        Schema::table('contacts', function (Blueprint $table) {
            // Composite/simple indexes for the columns ContactRepository
            // already filters on (status, subject_id) plus key_id lookups.
            $table->index('status', 'contacts_status_index');
            $table->index('subject_id', 'contacts_subject_id_index');
            $table->index('key_id', 'contacts_key_id_index');
            $table->index(['status', 'subject_id'], 'contacts_status_subject_id_index');

            // Foreign keys — nullOnDelete so removing a Subject/Key doesn't
            // cascade-delete the contact, it just detaches the reference.
            $table->foreign('subject_id', 'contacts_subject_id_foreign')
                ->references('id')->on('subjects')
                ->nullOnDelete();

            $table->foreign('key_id', 'contacts_key_id_foreign')
                ->references('id')->on('keys')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_subject_id_foreign');
            $table->dropForeign('contacts_key_id_foreign');

            $table->dropIndex('contacts_status_index');
            $table->dropIndex('contacts_subject_id_index');
            $table->dropIndex('contacts_key_id_index');
            $table->dropIndex('contacts_status_subject_id_index');
        });
    }
};
