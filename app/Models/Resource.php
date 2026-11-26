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
        'status',
        'is_returnable',
    ];

    protected $casts = [
        'is_returnable' => 'boolean',
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
}
