<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCambioCerrada extends Model
{
    use HasFactory;

    protected $table = 'solicitud_cambio_cerrada';

    protected $fillable = [
        'direccion',
        'cerradaproveniente_id',
        'cerradadestino_id',
        'proveniente',
        'destino',
        'user_solicitud',
        'estado',
        'comentariodestino',
        'comentarioproveniente',
    ];

    /**
     * Cerrada de origen (proveniente)
     */
    public function cerradaProveniente(): BelongsTo
    {
        return $this->belongsTo(Cerrada::class, 'cerradaproveniente_id');
    }

    /**
     * Cerrada de destino
     */
    public function cerradaDestino(): BelongsTo
    {
        return $this->belongsTo(Cerrada::class, 'cerradadestino_id');
    }

    /**
     * Usuario que solicita el cambio
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_solicitud');
    }
}
