<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marketing/attribution fields captured when a contact is created
     * (e.g. from a website form with UTM parameters). All optional strings.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('source')->nullable()->after('message');
            $table->string('utm_source')->nullable()->after('source');
            $table->string('utm_campaign')->nullable()->after('utm_source');
            $table->string('prod_category')->nullable()->after('utm_campaign');
            $table->string('utm_medium')->nullable()->after('prod_category');
            $table->string('source_page')->nullable()->after('utm_medium');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'utm_source',
                'utm_campaign',
                'prod_category',
                'utm_medium',
                'source_page',
            ]);
        });
    }
};
