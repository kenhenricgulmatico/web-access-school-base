<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestType extends Model
{
    use HasFactory;

    /**
     * Fixed IDs of the request types (inserted by the create_request_types_table migration).
     * Use these instead of writing 1 or 2 in the code:
     *     ResourceRequest::where('request_type_id', RequestType::FACILITY)
     */
    public const FACILITY = 1; // Facility Reservation
    public const MATERIAL = 2; // Material Request

    protected $fillable = [
        'type_name',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
}
