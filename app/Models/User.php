<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Request;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'status' ,'department_id', 'responsibility_center_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

public function auditLogs(): HasMany {
    return $this->hasMany(AuditLog::class);
}

public function responsibility(): BelongsTo
{
    return $this->belongsTo(ResponsibilityCenter::class, 'responsibility_center_id');
}

public function department(): BelongsTo
{
    return $this->belongsTo(Department::class);
}

public function notifications(): HasMany {
    return $this->hasMany(Notification::class);
}


public function resourceUsages(): HasMany {
    return $this->hasMany(ResourceUsage::class);
}

public function requests(): HasMany{
    return $this->hasMany(Request::class);
}

}
