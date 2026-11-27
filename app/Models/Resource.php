<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceFactory> */
    use HasFactory;

    protected $fillable = [
        'resource_type_id',
        'resource_name',
        'description',
        'unit',
        'quantity_available',
        'low_stock_at',
        'status',
        'is_returnable',
        // ✅ facility rooms
        'capacity',
        'location',
        'room_kind',
        'maintenance_reason',
        'maintenance_until',
    ];

    protected $casts = [
        'is_returnable'     => 'boolean',
        'maintenance_until' => 'date',
    ];

    /**
     * ✅ "Must be returned" always follows the type:
     *    Material = returned (chairs, projectors), Supplies / Facilities = not.
     */
    protected static function booted(): void
    {
        static::saving(function (Resource $resource) {
            if ($resource->isDirty('resource_type_id') || ! $resource->exists) {
                $type = ResourceType::find($resource->resource_type_id);
                $resource->is_returnable = (bool) $type?->isReturnable();
            }
        });
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ResourceAllLocation::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ResourceUsage::class);
    }

    public function requestsItems(): HasMany
    {
        return $this->hasMany(RequestItem::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function latestStock(): HasOne
    {
        return $this->hasOne(Stock::class)->latestOfMany();
    }

    /** ✅ Last time stock was ADDED (removals are stock rows with a negative quantity). */
    public function latestRestock(): HasOne
    {
        return $this->hasOne(Stock::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('quantity_added', '>', 0));
    }

    /** ✅ At or below its own "warn me" level (but not empty). */
    public function isLowStock(): bool
    {
        return $this->quantity_available > 0 && $this->quantity_available <= (int) ($this->low_stock_at ?? 5);
    }
}
