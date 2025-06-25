<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenAcceso extends Model
{
    use HasFactory;

    protected $table = 'tokens_acceso';

    protected $fillable = [
        'usuario_id',
        'tipo_token',
        'fecha_expiracion',
        'usos',
        'valor',
        'puerta',
    ];

    protected $casts = [
        'usuario_id' => 'integer',
        'tipo_token' => 'string',
        'fecha_expiracion' => 'datetime',
        'usos' => 'integer',
        'valor' => 'string',
        'puerta' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Verificar si el token está expirado
     */
    public function isExpired(): bool
    {
        if ($this->fecha_expiracion === null) {
            return false;
        }

        return now()->isAfter($this->fecha_expiracion);
    }

    /**
     * Verificar si el token tiene usos disponibles
     */
    public function hasUsesLeft(): bool
    {
        if ($this->usos === null) {
            return true;
        }

        return $this->usos > 0;
    }

    /**
     * Decrementar el número de usos
     */
    public function decrementUses(): void
    {
        if ($this->usos !== null && $this->usos > 0) {
            $this->usos--;
            $this->save();
        }
    }
}
