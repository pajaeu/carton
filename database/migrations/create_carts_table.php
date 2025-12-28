<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->decimal('exchange_rate', 16, 8)->nullable();
            $table->string('currency_code')->nullable();
            $table->integer('count')->nullable();
            $table->decimal('sub_total', 18, 6)->default(0);
            $table->decimal('sub_total_with_vat', 18, 6)->default(0);
            $table->decimal('grand_total', 18, 6)->default(0);
            $table->decimal('grand_total_with_vat', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);
            $table->decimal('vat_total', 18, 6)->default(0);
            $table->json('additional')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
