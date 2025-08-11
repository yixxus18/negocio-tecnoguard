<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TipoServicio extends Model
{
    use HasFactory;

    protected $table = 'tipo_Servicio';

    protected $fillable = [
        'nombreServicio'
    ];
    public function bitacora(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }
}
