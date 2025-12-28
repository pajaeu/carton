<p align="center">
    <img src="./art/logo.png" alt="Carton logo" height="60px">
</p>

<p align="center">
    <a href="https://packagist.org/packages/pajaeu/carton">
        <img src="https://img.shields.io/packagist/v/pajaeu/carton.svg?style=flat" alt="Packagist">
    </a>
    <a href="https://laravel.com">
        <img src="https://img.shields.io/badge/Laravel-12.0%2B-FF2D20?style=flat&logo=laravel" alt="Laravel version">
    </a>
    <a href="https://packagist.org/packages/pajaeu/carton">
        <img src="https://img.shields.io/packagist/dt/pajaeu/carton.svg?style=flat" alt="Total downloads">
    </a>
    <a href="https://packagist.org/packages/pajaeu/carton">
        <img src="https://img.shields.io/packagist/l/pajaeu/carton.svg?style=flat" alt="Total downloads">
    </a>
</p>

---

**Carton** is the missing cart package for Laravel.

## 📦 Installation

Install package via composer

```bash
composer require pajaeu/carton
```

Then publish configuration and migrations

```bash
php artisan carton:install
```

## 🚀 How to use Carton

```php
// create cart using custom currency code
Carton::createCart('CZK');

// if we do not pass currency code, it uses default one specified in config
Carton::createCart();

// then we need to create new data
$data = new CartLineData(
    'Product 1',
    300,
    21,
    [
        'size' => [
            'XS',
        ],
    ]
);

// so we can pass it to the addLine method also with the quantity parameter
Carton::addLine($data, 2);

// we can recalculate cart's totals so we have everything right (it is being made automatically when calling adding new line)
Carton::recalculate();

// then we can get the cart model and its properties
$cart = Carton::getCart();

echo 'Total items in cart: '.$cart->count;
echo 'Totals to pay: '.$cart->grand_total_with_vat.' '.$cart->currency_code;

// we can also get some current cart's properties using the facade
echo 'Totals to pay: '.Carton::getCartTotal().' '.Carton::getCartCurrencyCode();

// we can access lines on the cart model
$lines = $cart->lines;

// or you can get lines using the facade (returns empty collection if cart is not created yet)
$lines = Carton::getCartLines();
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
