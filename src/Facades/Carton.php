<?php

namespace Carton\Carton\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Carton\Carton\Carton
 */
class Carton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Carton\Carton\Carton::class;
    }
}
