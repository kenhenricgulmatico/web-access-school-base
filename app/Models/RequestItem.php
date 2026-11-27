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
        'stock_before_return',
        'stock_after_return',
        'stock_before_approval',
        'stock_after_approval',
        'approved_at',
        'assigned_at',
        'holder_id',
    ];

    protected $casts = [
        'due_back_at'      => 'date',
        'returned_at'      => 'datetime',
        'approved_at'      => 'datetime',
        'assigned_at'      => 'datetime',
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

    /** ✅ Who keeps an assigned item now. */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'holder_id');
    }

    public function handovers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemHandover::class)->latest('id');
    }

    /** ✅ Items someone keeps (no return date) and still has. One row per set of items. */
    public function scopeAssignedNow(Builder $query): Builder
    {
        return $query->whereNotNull('assigned_at')->whereNotNull('holder_id')->whereNull('returned_at');
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
