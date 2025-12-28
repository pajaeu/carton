<?php

declare(strict_types=1);

namespace Carton\Carton\Data;

final readonly class CartLineData
{
    /**
     * @param  array<string, mixed>  $additional
     */
    public function __construct(
        public string $title,
        public float $price,
        public float $vatRate,
        public array $additional = [],
        public bool $withVat = false,
    ) {}
}
