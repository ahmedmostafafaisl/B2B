<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{


    public function up(): void
    {
        // Update enum
        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM(
                'new',
                'in_progress',
                'contacted',
                'closed',
                'offer_price',
                'completed',
                'price_not_accepted'
            ) DEFAULT 'new'
        ");

        // Add note column
        Schema::table('contacts', function (Blueprint $table) {
            $table->text('note')->nullable()->after('offer_price');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM(
                'new',
                'in_progress',
                'contacted',
                'closed',
                'offer_price',
                'completed'
            ) DEFAULT 'new'
        ");
    }
};
