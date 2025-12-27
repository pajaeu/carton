<?php

declare(strict_types=1);

namespace Carton\Carton\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;

/**
 * @property-read int $id
 * @property bool $is_active
 * @property float $exchange_rate
 * @property string|null $currency_code
 * @property int $count
 * @property float $sub_total
 * @property float $sub_total_with_vat
 * @property float $grand_total
 * @property float $grand_total_with_vat
 * @property float $discount_total
 * @property float $vat_total
 * @property array $additional
 * @property int $user_id
 * @property-read User|null $user
 * @property-read Collection<int, CartLine> $lines
 */
final class Cart extends Model
{
    protected $guarded = [];

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

    /** @return HasMany<CartLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }
}
