<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurationPayDate extends Model
{
    use HasFactory;

    protected $table = 'configuration_pay_date';

    protected $fillable = [
        'nombre_configuracion',
        'Fecha_Corte',
        'pay',
        'tiempo_prorroga',
    ];

    protected $casts = [
        'Fecha_Corte' => 'integer',
        'pay' => 'integer',
        'tiempo_prorroga' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con cerradas
     */
    public function cerradas(): HasMany
    {
        return $this->hasMany(Cerrada::class, 'configuration_pay_date');
    }
}
