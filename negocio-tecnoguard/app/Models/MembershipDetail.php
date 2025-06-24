<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipDetail extends Model
{
    use HasFactory;

    protected $table = 'membership_details';

    protected $fillable = [
        'membership_id',
        'amount',
        'date_pay',
        'date_finalization',
        'ticket',
        'estatus',
    ];

    protected $casts = [
        'membership_id' => 'integer',
        'amount' => 'decimal:2',
        'date_pay' => 'date',
        'date_finalization' => 'date',
        'ticket' => 'string',
        'estatus' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con membresía
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
