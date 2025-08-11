<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cerrada extends Model
{
    use HasFactory;

    protected $table = 'cerradas';

    protected $fillable = [
        'group_name',
        'description',
        'configuration_pay_date',
        'guard_id',
        'jefe_cerrada_id'
    ];

    protected $casts = [
        'configuration_pay_date' => 'integer',
        'guard_id' => 'integer',
        
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con configuración de pagos
     */
    public function configurationPayDate(): BelongsTo
    {
        return $this->belongsTo(ConfigurationPayDate::class, 'configuration_pay_date');
    }

    /**
     * Relación con guardia asignado
     */
    public function assignedGuard(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    /**
     * Relación con grupos familiares
     */
    public function familyGroups(): HasMany
    {
        return $this->hasMany(FamilyGroup::class);
    }

    /**
     * Relación con localidades de entrada (muchos a muchos)
     */
    public function localidadesEntradas(): BelongsToMany
    {
        return $this->belongsToMany(LocalidadEntrada::class, 'entrada_cerradas', 'cerrada_id', 'entrada_id');
    }
   public function catalogoDispositivos(): HasMany
    {
        return $this->hasMany(CatalogoDispositivo::class, 'cerrada_id');
    }


    public function solicitudesProvenientes(): HasMany
{
    return $this->hasMany(SolicitudCambioCerrada::class, 'cerradaproveniente_id');
}

public function bitacora(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }

/**
 * Solicitudes donde esta cerrada es la de destino
 */
public function solicitudesDestinos(): HasMany
{
    return $this->hasMany(SolicitudCambioCerrada::class, 'cerradadestino_id');
}
}
