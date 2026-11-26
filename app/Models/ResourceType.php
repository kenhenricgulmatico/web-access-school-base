<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ✅ Only three stock types:
 *   - Supplies   → used up, never returned (bond paper, markers…)
 *   - Material   → borrowed and RETURNED (chairs, projectors…)
 *   - Facilities → rooms / labs that are reserved, not stocked
 */
class ResourceType extends Model
{
    use HasFactory;

    public const SUPPLIES = 'Supplies';
    public const MATERIAL = 'Material';
    public const FACILITY = 'Facilities';

    /** Types the Admin can pick on the Stock Materials page (facilities are managed elsewhere). */
    public const STOCK_TYPES = [self::SUPPLIES, self::MATERIAL];

    protected $fillable = [
        'type_name',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /** Items of this type must be given back. */
    public function isReturnable(): bool
    {
        return $this->type_name === self::MATERIAL;
    }

    public static function facilityId(): ?int
    {
        return static::where('type_name', self::FACILITY)->value('id');
    }
}
