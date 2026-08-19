<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up(): void
    {
        Schema::table('contact_logs', function (Blueprint $table) {
            $table->string('action')->nullable()->after('new_values');
            $table->json('meta')->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('contact_logs', function (Blueprint $table) {
            $table->dropColumn(['action', 'meta']);
        });
    }
};
