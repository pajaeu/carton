<?php

declare(strict_types=1);

namespace Carton\Carton\Facades;

use Carton\Carton\Data\CartLineData;
use Carton\Carton\Models\Cart;
use Carton\Carton\Models\CartLine;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void init()
 * @method static Cart|null getCart()
 * @method static void setCart(Cart $cart)
 * @method static Collection<int, CartLine> getCartLines()
 * @method static float getCartSubtotal(bool $withVat = true)
 * @method static float getCartTotal(bool $withVat = true)
 * @method static string getCartCurrencyCode()
 * @method static Cart createCart(?string $currencyCode = null)
 * @method static void destroyCart(Cart $cart)
 * @method static void mergeUserCart(Authenticatable $user)
 * @method static CartLine addLine(CartLineData $data, int $quantity = 1)
 * @method static void removeLine(int $id)
 * @method static void updateLineQuantity(CartLine $line, int $quantity)
 * @method static void recalculate()
 *
 * @see \Carton\Carton\Carton
 */
final class Carton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Carton\Carton\Carton::class;
    }
}
