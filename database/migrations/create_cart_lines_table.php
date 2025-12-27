<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class
{
    public function up(): void
    {
        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->string('title');
            $table->smallInteger('quantity')->unsigned();
            $table->decimal('vat_rate', 10)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('price_with_vat', 18, 6)->default(0);
            $table->decimal('total', 18, 6)->default(0);
            $table->decimal('total_with_vat', 18, 6)->default(0);
            $table->json('additional')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_lines');
    }
};
