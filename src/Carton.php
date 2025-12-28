<?php

declare(strict_types=1);

namespace Carton\Carton;

use Carton\Carton\Data\CartLineData;
use Carton\Carton\Models\Cart;
use Carton\Carton\Models\CartLine;
use Exception;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

final class Carton
{
    private const string SESSION_KEY = '_carton_cart_id';

    private ?Cart $cart = null;

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->cart = Cart::query()
                ->where('is_active', true)
                ->where('user_id', $user->id)
                ->first();

            return;
        }

        if (session()->has(self::SESSION_KEY)) {
            $this->cart = Cart::query()
                ->where('is_active', true)
                ->find(session(self::SESSION_KEY));
        }
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;

        if ($this->cart->user) {
            return;
        }

        session()->put(self::SESSION_KEY, $this->cart->id);
    }

    public function getCartLines(): Collection
    {
        if (! $this->cart instanceof Cart) {
            return collect([]);
        }

        return $this->cart->lines;
    }

    public function getCartSubtotal(bool $withVat = true): float
    {
        if (! $this->cart instanceof Cart) {
            return 0.00;
        }

        return $withVat ? $this->cart->sub_total_with_vat : $this->cart->sub_total;
    }

    public function getCartTotal(bool $withVat = true): float
    {
        if (! $this->cart instanceof Cart) {
            return 0.00;
        }

        return $withVat ? $this->cart->grand_total_with_vat : $this->cart->grand_total;
    }

    public function getCartCurrencyCode(): string
    {
        if (! $this->cart instanceof Cart) {
            return '';
        }

        return $this->cart->currency_code;
    }

    public function createCart(?string $currencyCode = null): Cart
    {
        $currencyCode ??= config('carton.default_currency.code');

        $data = [
            'is_active' => true,
            'currency_code' => $currencyCode,
            'user_id' => auth()->id(),
        ];

        $cart = Cart::query()->create($data);

        $this->setCart($cart);

        return $cart;
    }

    public function destroyCart(Cart $cart): void
    {
        $cart->delete();

        if (session()->has(self::SESSION_KEY)) {
            session()->forget(self::SESSION_KEY);
        }

        $this->cart = null;
    }

    public function mergeUserCart(User $user): void
    {
        if (! session()->has(self::SESSION_KEY)) {
            return;
        }

        $cart = Cart::query()
            ->where('is_active', true)
            ->where('user_id', $user->id)
            ->first();

        $guestCart = Cart::query()->find(session(self::SESSION_KEY));

        if (! $guestCart) {
            return;
        }

        if (! $cart) {
            $guestCart->update([
                'user_id' => $user->id,
            ]);

            session()->forget(self::SESSION_KEY);

            return;
        }

        $this->setCart($cart);

        foreach ($guestCart->lines as $guestCartLine) {
            try {
                $this->addLine(new CartLineData(
                    $guestCartLine->title,
                    $guestCartLine->price,
                    $guestCartLine->vat_rate,
					$guestCartLine->additional
                ), $guestCartLine->quantity);
            } catch (Exception $e) {
                report($e);
            }
        }

        $this->recalculate();

        $this->destroyCart($guestCart);
    }

    public function addLine(CartLineData $data, int $quantity = 1): CartLine
    {
        if (! $this->cart instanceof Cart) {
            $this->createCart();
        }

        if ($data->withVat) {
            $priceWithVat = $data->price;
            $price = $priceWithVat / (1 + $data->vatRate / 100);
        } else {
            $price = $data->price;
            $priceWithVat = $price * (1 + $data->vatRate / 100);
        }

        $line = $this->cart->lines()->create([
            'title' => $data->title,
            'quantity' => $quantity,
            'vat_rate' => $data->vatRate,
            'price' => $price,
            'price_with_vat' => $priceWithVat,
            'total' => $price * $quantity,
            'total_with_vat' => $priceWithVat * $quantity,
            'additional' => $data->additional,
        ]);

        $this->recalculate();

        return $line;
    }

    public function updateLineQuantity(CartLine $line, int $quantity): bool
    {
        return $line->update([
            'quantity' => $quantity,
            'total' => $quantity * $line->price,
            'total_with_vat' => $quantity * $line->price_with_vat,
        ]);
    }

    public function recalculate(): void
    {
        if (! $this->cart instanceof Cart) {
            return;
        }

        $count = $this->cart->lines()->sum('quantity');
        $subTotal = 0;
        $subTotalWithVat = 0;
        $grandTotal = 0;
        $grandTotalWithVat = 0;
        $vatTotal = 0;

        foreach ($this->cart->lines as $line) {
            $subTotal += $line->total;
            $subTotalWithVat += $line->total_with_vat;
            $grandTotal += $line->total_with_vat;
            $grandTotalWithVat += $line->total_with_vat;
            $vatTotal += $line->total_with_vat - $line->total;
        }

        $this->cart->count = $count;
        $this->cart->sub_total = $subTotal;
        $this->cart->sub_total_with_vat = $subTotalWithVat;
        $this->cart->grand_total = $grandTotal;
        $this->cart->grand_total_with_vat = $grandTotalWithVat;
        $this->cart->vat_total = $vatTotal;

        $this->cart->save();
    }
}
