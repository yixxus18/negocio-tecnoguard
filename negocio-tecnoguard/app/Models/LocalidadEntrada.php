<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LocalidadEntrada extends Model
{
    use HasFactory;

    protected $table = 'localidades_entradas';

    protected $fillable = [
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'latitud' => 'double',
        'longitud' => 'double',
    ];

    /**
     * Relación con cerradas (muchos a muchos)
     */
    public function cerradas(): BelongsToMany
    {
        return $this->belongsToMany(Cerrada::class, 'entrada_cerradas', 'entrada_id', 'cerrada_id');
    }
}
