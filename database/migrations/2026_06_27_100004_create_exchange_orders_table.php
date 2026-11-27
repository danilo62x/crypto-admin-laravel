<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pair', 20);   // BTC/BRL, ETH/BRL, etc.
            $table->string('side', 10);   // compra | venda
            $table->decimal('price', 28, 8);
            $table->decimal('amount', 28, 8);
            $table->decimal('total', 28, 8)->default(0);
            $table->string('status')->default('pendente'); // pendente | executada | cancelada
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_orders');
    }
};
