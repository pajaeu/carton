<?php

declare(strict_types=1);

namespace Carton\Carton\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $quantity
 * @property float $price
 * @property float $price_with_vat
 * @property float $total
 * @property float $total_with_vat
 * @property array $additional
 * @property int $cart_id
 * @property-read Cart|null $cart
 */
final class CartLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'vat_rate' => 'float',
        'price' => 'float',
        'price_with_vat' => 'float',
        'total' => 'float',
        'total_with_vat' => 'float',
        'additional' => 'array',
    ];

    /** @return BelongsTo<Cart, $this> */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}
