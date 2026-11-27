<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type');   // deposito | saque | trade
            $table->decimal('amount', 28, 8);
            $table->decimal('fee', 28, 8)->default(0);
            $table->string('status')->default('pendente'); // pendente | concluida | falha
            $table->string('tx_hash')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
