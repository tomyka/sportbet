<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    public const CREATED_AT = 'occurred_at'; // migration uses occurred_at, not created_at
    public const UPDATED_AT = null;          // append-only, no updated_at

    protected $fillable = [
        'actor_user_id',
        'action',
        'subject_type',
        'subject_id',
        'changes',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
