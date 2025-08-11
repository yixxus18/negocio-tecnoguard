<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bitacora extends Model
{
     use HasFactory;

    protected $table = 'bitacora';

    protected $fillable = [
        'descripcion',
        'fecha_asignacion',
        'fecha_programada',
        'fecha_finalizacion',
        'prioridad',
        'tiposervicio_id',
        'tecnico_id',
        'cerrada_id',
        'status',
        'comentario'
    ];
    public function tenico(): BelongsTo
    {
        return $this->belongsTo(User::class,'tecnico_id');
    }
    public function cerrada():BelongsTo
    {
        return $this->belongsTo(Cerrada::class,'cerrada_id');
    }


    public function servicio():BelongsTo
    {
        return $this->belongsTo(TipoServicio::class,'tiposervicio_id');
    }
}
