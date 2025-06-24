<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Membership extends Model
{
    use HasFactory;

    protected $table = 'memberships';

    protected $fillable = [
        'last_pay',
        'next_pay',
        'is_active',
    ];

    protected $casts = [
        'last_pay' => 'date',
        'next_pay' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con detalles de membresía
     */
    public function membershipDetails(): HasMany
    {
        return $this->hasMany(MembershipDetail::class);
    }

    /**
     * Relación con grupos familiares
     */
    public function familyGroups(): HasMany
    {
        return $this->hasMany(FamilyGroup::class);
    }
}
