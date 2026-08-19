<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;
    use HasActivityLogs;

    protected $fillable = [
        'subject_id',
        'key_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'offer_price',
        'completed_at',
        'note',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }

    /**
     * Legacy, Contact-only log table. Kept read-only for reference — all
     * of its rows have been copied into activityLogs() (see the
     * 2026_08_19_120300_migrate_contact_logs_to_activity_logs migration),
     * which is now the relation actually written to and read from.
     */
    public function legacyLogs(): HasMany
    {
        return $this->hasMany(ContactLog::class);
    }
}
