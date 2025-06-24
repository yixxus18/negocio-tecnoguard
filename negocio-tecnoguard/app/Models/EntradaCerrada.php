<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EntradaCerrada extends Pivot
{
    protected $table = 'entrada_cerradas';

    public $timestamps = false;

    protected $fillable = [
        'cerrada_id',
        'entrada_id',
    ];

    protected $casts = [
        'cerrada_id' => 'integer',
        'entrada_id' => 'integer',
    ];
}
