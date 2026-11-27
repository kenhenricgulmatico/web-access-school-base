<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One change of hands of an assigned item (assigned → handed over → returned). */
class ItemHandover extends Model
{
    protected $fillable = ['request_item_id', 'from_user_id', 'to_user_id', 'by_user_id', 'kind', 'note'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RequestItem::class, 'request_item_id');
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}
