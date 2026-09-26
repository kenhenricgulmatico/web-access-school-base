<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestApproval extends Model
{

    use HasFactory;

    protected $fillable = [
        'request_id',
        'approver_id',
        'status',
        'remarks',
        'approved_at',
    ];

    public function request():BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function approver():BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
