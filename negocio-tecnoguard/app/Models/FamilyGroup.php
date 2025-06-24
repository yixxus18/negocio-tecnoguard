<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyGroup extends Model
{
    use HasFactory;

    protected $table = 'family_groups';

    protected $fillable = [
        'membership_id',
        'cerrada_id',
        'is_active',
    ];

    protected $casts = [
        'membership_id' => 'integer',
        'cerrada_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con membresía
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Relación con cerrada
     */
    public function cerrada(): BelongsTo
    {
        return $this->belongsTo(Cerrada::class);
    }

    /**
     * Relación con usuarios (miembros de la familia)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'family_id');
    }
}
