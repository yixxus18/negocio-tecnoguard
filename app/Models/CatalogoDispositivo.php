<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoDispositivo extends Model
{
    use HasFactory;

    protected $table = 'catalogo_dispositivos';

    protected $fillable = [
        'cerrada_id',
        'tecnico_id',
        'archivo_configuracion',
    ];

    /**
     * Relación con la tabla cerradas
     */
    public function cerrada(): BelongsTo
    {
        return $this->belongsTo(Cerrada::class, 'cerrada_id');
    }

    /**
     * Relación con la tabla users (técnico)
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    /**
     * Detalles asociados (UID y nombre)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DispositivoDetalle::class, 'catalogo_id');
    }
}
