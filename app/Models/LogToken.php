<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;



class LogToken extends Model
{
    protected $connection = "mongodb";
    protected $collection = "log_tokens";
    protected $fillable = [
        'token',
        'used_at',
        'created_by',
        'nombre'
    ] ;
}
