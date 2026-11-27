<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type   = $this->faker->randomElement(['deposito', 'deposito', 'saque', 'trade']);
        $status = $this->faker->randomElement(['concluida', 'concluida', 'concluida', 'pendente', 'falha']);
        $amount = $this->faker->randomFloat(8, 0.0001, 5);

        return [
            'wallet_id'    => \App\Models\Wallet::factory(),
            'type'         => $type,
            'amount'       => $amount,
            'fee'          => round($amount * 0.001, 8),
            'status'       => $status,
            'tx_hash'      => '0x' . strtolower(Str::random(64)),
            'notes'        => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'confirmed_at' => $status === 'concluida'
                ? $this->faker->dateTimeBetween('-6 months', 'now')
                : null,
            'created_at'   => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
