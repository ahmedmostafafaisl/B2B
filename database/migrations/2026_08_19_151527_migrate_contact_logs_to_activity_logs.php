<?php

use App\Models\Contact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Copies every existing contact_logs row into activity_logs so the
     * Contact audit trail keeps its full history after the switch to the
     * generic, polymorphic logging system. contact_logs itself is left in
     * place (not dropped) as a historical backup.
     */
    public function up(): void
    {
        DB::table('contact_logs')->orderBy('id')->chunk(500, function ($rows) {
            $now = now();

            $mapped = $rows->map(function ($row) use ($now) {
                return [
                    'loggable_type' => Contact::class,
                    'loggable_id'   => $row->contact_id,
                    'user_id'       => $row->user_id,
                    'action'        => $row->action ?? 'update',
                    'old_values'    => $row->old_values,
                    'new_values'    => $row->new_values,
                    'note'          => $row->note,
                    'meta'          => $row->meta,
                    'created_at'    => $row->created_at ?? $now,
                    'updated_at'    => $row->updated_at ?? $now,
                ];
            })->toArray();

            if (!empty($mapped)) {
                DB::table('activity_logs')->insert($mapped);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes only the rows this migration created (all Contact activity
     * logs), rather than every activity_logs row, in case other models
     * have already logged activity by the time this is rolled back.
     */
    public function down(): void
    {
        DB::table('activity_logs')
            ->where('loggable_type', Contact::class)
            ->delete();
    }
};
