<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'institution_name',
        'address',
        'city',
        'district',
        'street',
        'postal_code',
        'password',
        'role',
        'admin_permissions',
        'is_active',
        'login_blocked',
        'account_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

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
            'admin_permissions' => 'array',
            'is_active' => 'boolean',
            'login_blocked' => 'boolean',
            'account_verified_at' => 'datetime',
        ];
    }

    public function canLogin(): bool
    {
        return $this->is_active && ! $this->login_blocked;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->role !== 'admin') {
            return false;
        }

        if ($this->admin_permissions === null) {
            return true;
        }

        return in_array($permission, $this->admin_permissions, true);
    }

    public function hasAnyAdminPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasAdminPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
