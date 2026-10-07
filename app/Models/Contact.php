<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'source',
        'utm_source',
        'utm_campaign',
        'prod_category',
        'utm_medium',
        'source_page',
        'status',
        'offer_price',
        'completed_at',
        'note',
    ];

    protected $casts = [
        'offer_price' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * The most recently created Task linked to this contact. Contact has no
     * creator of its own, so "created_by"/"assigned_to" on a contact are
     * derived from this task instead.
     */
    public function latestTask(): HasOne
    {
        return $this->hasOne(Task::class)->latestOfMany();
    }

    /**
     * A contact can have one or more closing reasons.
     */
    public function closingReasons(): BelongsToMany
    {
        return $this->belongsToMany(ClosingReason::class, 'contact_closing_reasons')->withTimestamps();
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
