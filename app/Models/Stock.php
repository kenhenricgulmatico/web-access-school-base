<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    /** @use HasFactory<\Database\Factories\StockFactory> */
    use HasFactory;
    protected $fillable = [
        'resource_id',
        'user_id',
        'quantity_added',
        'quantity_before',
        'quantity_after',
        'supplier',
        'unit_price',
        'arrival_date',
        'arrival_time',
        'remarks',
        'reason',
    ];

    /** ✅ Why stock was removed (a removal = negative quantity_added). */
    public const REASONS = [
        'used_up'    => 'Used up',
        'damaged'    => 'Damaged',
        'lost'       => 'Lost',
        'correction' => 'Count correction',
    ];

    protected $casts = [
        'arrival_date' => 'date',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
