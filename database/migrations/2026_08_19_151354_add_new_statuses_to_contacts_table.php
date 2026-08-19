<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Full enum lists kept in one place so up()/down() stay symmetric with
     * the previous status migrations.
     */
    private array $previousStatuses = [
        'new',
        'in_progress',
        'contacted',
        'closed',
        'offer_price',
        'completed',
        'price_not_accepted',
    ];

    private array $newStatuses = [
        'not_serious',
        'needs_follow_up',
        'no_response',
        'awaiting_response',
        'unable_to_contact',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $all = implode("','", array_merge($this->previousStatuses, $this->newStatuses));

        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM('{$all}') NOT NULL DEFAULT 'new'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * Any contact already sitting on one of the new statuses is moved back
     * to 'new' first, since MySQL will silently null/truncate an enum value
     * that no longer exists in the list otherwise.
     */
    public function down(): void
    {
        DB::table('contacts')
            ->whereIn('status', $this->newStatuses)
            ->update(['status' => 'new']);

        $previous = implode("','", $this->previousStatuses);

        DB::statement("
            ALTER TABLE contacts
            MODIFY COLUMN status ENUM('{$previous}') NOT NULL DEFAULT 'new'
        ");
    }
};
