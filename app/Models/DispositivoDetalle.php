<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispositivoDetalle extends Model
{
    use HasFactory;

    protected $table = 'dispositivosdetalles';

    protected $fillable = [
        'catalogo_id',
        'uid',
        'nombre_dispositivo',
        'pin'
    ];

    /**
     * Pertenencia al catálogo principal
     */
    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(CatalogoDispositivo::class, 'catalogo_id');
    }
}
