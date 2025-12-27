<?php

namespace Carton\Carton\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate' => 'float',
        'count' => 'integer',
        'sub_total' => 'float',
        'sub_total_with_vat' => 'float',
        'grand_total' => 'float',
        'grand_total_with_vat' => 'float',
        'discount_total' => 'float',
        'vat_total' => 'float',
        'additional' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
