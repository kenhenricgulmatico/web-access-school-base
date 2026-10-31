<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestItem extends Model
{

    use HasFactory;

    /** Conditions a returned item can be in. */
    public const CONDITIONS = [
        'good'    => 'Good',
        'damaged' => 'Damaged',
        'lost'    => 'Lost',
    ];

    protected $fillable = [
        'request_id',
        'resource_id',
        'item_name',
        'quantity',
        'request_date',
        'start_time',
        'end_time',
        'due_back_at',
        'returned_at',
        'return_condition',
        'return_notes',
        'received_by',
        'reminder_sent_at',
    ];

    protected $casts = [
        'due_back_at'      => 'date',
        'returned_at'      => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function request():BelongsTo
    {
        return $this->belongsTo(Request::class);
    }
    public function resource():BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** Borrowed items that still have to be given back. */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereNotNull('due_back_at')->whereNull('returned_at');
    }

    /** Outstanding items whose return date has passed. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->outstanding()->whereDate('due_back_at', '<', today());
    }

    public function isOverdue(): bool
    {
        return $this->due_back_at && ! $this->returned_at && $this->due_back_at->lt(today());
    }
}
