<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'family_id',
        'is_active',
        'family_group_isactive',
        'direccion',
        'direccion_verified',
        'code',
        'role_id',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
        'two_factor_expires_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'family_id' => 'integer',
            'is_active' => 'boolean',
            'family_group_isactive' => 'boolean',
            'direccion_verified' => 'boolean',
            'role_id' => 'integer',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $attributes = [
        'two_factor_enabled' => true
    ];

    /**
     * Relación con rol
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación con grupo familiar
     */
    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class, 'family_id');
    }

    /**
     * Relación con tokens de acceso
     */
    public function tokensAcceso(): HasMany
    {
        return $this->hasMany(TokenAcceso::class, 'usuario_id');
    }

    /**
     * Relación con cerradas donde es guardia
     */
    public function cerradasAsGuard(): HasMany
    {
        return $this->hasMany(Cerrada::class, 'guard_id');
    }
}
