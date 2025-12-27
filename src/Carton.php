<?php

declare(strict_types=1);

namespace Carton\Carton;

use Carton\Carton\Models\Cart;

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
}
