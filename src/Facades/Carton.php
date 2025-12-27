<?php

declare(strict_types=1);

namespace Carton\Carton\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Carton\Carton\Carton
 */
final class Carton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Carton\Carton\Carton::class;
    }
}
