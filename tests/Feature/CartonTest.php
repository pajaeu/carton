<?php

declare(strict_types=1);

use Carton\Carton\Data\CartLineData;
use Carton\Carton\Facades\Carton;
use Carton\Carton\Models\Cart;
use Carton\Carton\Models\CartLine;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Create users table for testing authenticated scenarios
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
});

describe('Cart creation', function () {
    it('can create a cart with default currency', function () {
        $cart = Carton::createCart();

        expect($cart)
            ->toBeInstanceOf(Cart::class)
            ->and($cart->is_active)->toBeTrue()
            ->and($cart->currency_code)->toBe('EUR')
            ->and($cart->user_id)->toBeNull();
    });

    it('can create a cart with custom currency', function () {
        $cart = Carton::createCart('USD');

        expect($cart->currency_code)->toBe('USD');
    });

    it('stores cart id in session for guest users', function () {
        $cart = Carton::createCart();

        expect(session('_carton_cart_id'))->toBe($cart->id);
    });

    it('associates cart with authenticated user', function () {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        Auth::login($user);

        $cart = Carton::createCart();

        expect($cart->user_id)->toBe($user->id);
    });
});

describe('Cart retrieval', function () {
    it('returns null when no cart exists', function () {
        $carton = new \Carton\Carton\Carton();

        expect($carton->getCart())->toBeNull();
    });

    it('can get the current cart', function () {
        $cart = Carton::createCart();

        expect(Carton::getCart())
            ->toBeInstanceOf(Cart::class)
            ->and(Carton::getCart()->id)->toBe($cart->id);
    });

    it('can set a cart', function () {
        $cart = Cart::create([
            'is_active' => true,
            'currency_code' => 'GBP',
        ]);

        Carton::setCart($cart);

        expect(Carton::getCart()->id)->toBe($cart->id);
    });
});

describe('Cart lines', function () {
    it('returns empty collection when no cart exists', function () {
        $carton = new \Carton\Carton\Carton();

        expect($carton->getCartLines())->toBeEmpty();
    });

    it('can add a line to cart', function () {
        Carton::createCart();

        $lineData = new CartLineData(
            title: 'Test Product',
            price: 100.00,
            vatRate: 21.0,
        );

        $line = Carton::addLine($lineData);

        expect($line)
            ->toBeInstanceOf(CartLine::class)
            ->and($line->title)->toBe('Test Product')
            ->and($line->quantity)->toBe(1)
            ->and($line->price)->toBe(100.00)
            ->and($line->vat_rate)->toBe(21.0);
    });

    it('calculates VAT correctly when price is without VAT', function () {
        Carton::createCart();

        $lineData = new CartLineData(
            title: 'Test Product',
            price: 100.00,
            vatRate: 21.0,
            withVat: false,
        );

        $line = Carton::addLine($lineData);

        expect($line->price)->toBe(100.00)
            ->and($line->price_with_vat)->toBe(121.00)
            ->and($line->total)->toBe(100.00)
            ->and($line->total_with_vat)->toBe(121.00);
    });

    it('calculates VAT correctly when price includes VAT', function () {
        Carton::createCart();

        $lineData = new CartLineData(
            title: 'Test Product',
            price: 121.00,
            vatRate: 21.0,
            withVat: true,
        );

        $line = Carton::addLine($lineData);

        expect($line->price)->toBe(100.00)
            ->and($line->price_with_vat)->toBe(121.00);
    });

    it('can add line with custom quantity', function () {
        Carton::createCart();

        $lineData = new CartLineData(
            title: 'Test Product',
            price: 50.00,
            vatRate: 21.0,
        );

        $line = Carton::addLine($lineData, 3);

        expect($line->quantity)->toBe(3)
            ->and($line->total)->toBe(150.00)
            ->and($line->total_with_vat)->toBe(181.50);
    });

    it('can add line with additional data', function () {
        Carton::createCart();

        $lineData = new CartLineData(
            title: 'Test Product',
            price: 100.00,
            vatRate: 21.0,
            additional: ['sku' => 'TEST-001', 'color' => 'red'],
        );

        $line = Carton::addLine($lineData);

        expect($line->additional)
            ->toBe(['sku' => 'TEST-001', 'color' => 'red']);
    });

    it('creates cart automatically when adding line without existing cart', function () {
        $lineData = new CartLineData(
            title: 'Test Product',
            price: 100.00,
            vatRate: 21.0,
        );

        $line = Carton::addLine($lineData);

        expect(Carton::getCart())->not->toBeNull()
            ->and($line->cart_id)->toBe(Carton::getCart()->id);
    });

    it('can get all cart lines', function () {
        Carton::createCart();

        Carton::addLine(new CartLineData('Product 1', 100.00, 21.0));
        Carton::addLine(new CartLineData('Product 2', 200.00, 21.0));
        Carton::addLine(new CartLineData('Product 3', 300.00, 21.0));

        Carton::getCart()->refresh();

        $lines = Carton::getCartLines();

        expect($lines)->toHaveCount(3)
            ->and($lines->pluck('title')->toArray())
            ->toBe(['Product 1', 'Product 2', 'Product 3']);
    });

    it('can update line quantity', function () {
        Carton::createCart();

        $line = Carton::addLine(new CartLineData('Product', 100.00, 21.0));

        Carton::updateLineQuantity($line, 5);

        $line->refresh();

        expect($line->quantity)->toBe(5)
            ->and($line->total)->toBe(500.00)
            ->and($line->total_with_vat)->toBe(605.00);
    });
});

describe('Cart totals', function () {
    it('returns zero subtotal when no cart exists', function () {
        $carton = new \Carton\Carton\Carton();

        expect($carton->getCartSubtotal())->toBe(0.00)
            ->and($carton->getCartSubtotal(false))->toBe(0.00);
    });

    it('returns zero total when no cart exists', function () {
        $carton = new \Carton\Carton\Carton();

        expect($carton->getCartTotal())->toBe(0.00)
            ->and($carton->getCartTotal(false))->toBe(0.00);
    });

    it('calculates cart subtotal correctly', function () {
        Carton::createCart();

        Carton::addLine(new CartLineData('Product 1', 100.00, 21.0), 2);
        Carton::addLine(new CartLineData('Product 2', 50.00, 21.0), 1);

        Carton::getCart()->refresh();

        Carton::recalculate();

        expect(Carton::getCartSubtotal(false))->toBe(250.00)
            ->and(Carton::getCartSubtotal())->toBe(302.50);
    });

    it('calculates cart total correctly', function () {
        Carton::createCart();

        Carton::addLine(new CartLineData('Product 1', 100.00, 21.0), 2);
        Carton::addLine(new CartLineData('Product 2', 50.00, 21.0), 1);

        Carton::getCart()->refresh();

        Carton::recalculate();

        expect(Carton::getCartTotal())->toBe(302.50);
    });
});

describe('Cart currency', function () {
    it('returns empty string when no cart exists', function () {
        $carton = new \Carton\Carton\Carton();

        expect($carton->getCartCurrencyCode())->toBe('');
    });

    it('returns cart currency code', function () {
        Carton::createCart('CZK');

        expect(Carton::getCartCurrencyCode())->toBe('CZK');
    });
});

describe('Cart recalculation', function () {
    it('recalculates cart totals after adding lines', function () {
        $cart = Carton::createCart();

        Carton::addLine(new CartLineData('Product 1', 100.00, 21.0));

        $cart->refresh();

        expect($cart->count)->toBe(1)
            ->and($cart->sub_total)->toBe(100.00)
            ->and($cart->sub_total_with_vat)->toBe(121.00)
            ->and($cart->vat_total)->toBe(21.00);
    });

    it('updates cart count with quantities', function () {
        $cart = Carton::createCart();

        Carton::addLine(new CartLineData('Product 1', 100.00, 21.0), 3);
        Carton::addLine(new CartLineData('Product 2', 50.00, 21.0), 2);

        $cart->refresh();

        expect($cart->count)->toBe(5);
    });
});

describe('Cart destruction', function () {
    it('can destroy a cart', function () {
        $cart = Carton::createCart();
        $cartId = $cart->id;

        Carton::destroyCart($cart);

        expect(Cart::find($cartId))->toBeNull()
            ->and(Carton::getCart())->toBeNull()
            ->and(session('_carton_cart_id'))->toBeNull();
    });

    it('clears session when destroying guest cart', function () {
        $cart = Carton::createCart();

        expect(session('_carton_cart_id'))->not->toBeNull();

        Carton::destroyCart($cart);

        expect(session('_carton_cart_id'))->toBeNull();
    });
});

describe('Cart initialization', function () {
    it('initializes cart from session for guest users', function () {
        $cart = Carton::createCart();
        $cartId = $cart->id;

        $carton = new \Carton\Carton\Carton();

        expect($carton->getCart())->not->toBeNull()
            ->and($carton->getCart()->id)->toBe($cartId);
    });

    it('initializes cart for authenticated user', function () {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        Auth::login($user);

        $cart = Carton::createCart();

        session()->forget('_carton_cart_id');

        $carton = new \Carton\Carton\Carton();

        expect($carton->getCart())->not->toBeNull()
            ->and($carton->getCart()->id)->toBe($cart->id);
    });

    it('only loads active carts', function () {
        $cart = Carton::createCart();
        $cart->update(['is_active' => false]);

        $carton = new \Carton\Carton\Carton();

        expect($carton->getCart())->toBeNull();
    });
});

describe('Cart merging', function () {
    it('assigns guest cart to user when user has no cart', function () {
        $guestCart = Carton::createCart();

        Carton::addLine(new CartLineData('Guest Product', 100.00, 21.0));

        $guestCartId = $guestCart->id;

        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        Carton::mergeUserCart($user);

        $guestCart->refresh();

        expect($guestCart->user_id)->toBe($user->id)
            ->and(session('_carton_cart_id'))->toBeNull();
    });

    it('merges guest cart lines into existing user cart', function () {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        Auth::login($user);

        $userCart = Carton::createCart();

        Carton::addLine(new CartLineData('User Product', 100.00, 21.0));

        Auth::logout();

        session()->forget('_carton_cart_id');

        $guestCart = Cart::create([
            'is_active' => true,
            'currency_code' => 'EUR',
        ]);

        $guestCart->lines()->create([
            'title' => 'Guest Product',
            'quantity' => 2,
            'vat_rate' => 21.0,
            'price' => 50.00,
            'price_with_vat' => 60.50,
            'total' => 100.00,
            'total_with_vat' => 121.00,
            'additional' => [],
        ]);

        session()->put('_carton_cart_id', $guestCart->id);

        Carton::mergeUserCart($user);

        $userCart->refresh();

        expect($userCart->lines)->toHaveCount(2)
            ->and(Cart::find($guestCart->id))->toBeNull();
    });

    it('does nothing when no guest cart exists', function () {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        session()->forget('_carton_cart_id');

        Carton::mergeUserCart($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });
});
