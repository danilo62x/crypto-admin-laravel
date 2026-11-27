<?php

namespace Database\Factories;

use App\Models\ExchangeOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeOrder>
 */
class ExchangeOrderFactory extends Factory
{
    protected $model = ExchangeOrder::class;

    private static array $pairs = [
        'BTC/BRL', 'ETH/BRL', 'SOL/BRL', 'BNB/BRL', 'ADA/BRL', 'XRP/BRL',
    ];

    public function definition(): array
    {
        $pair   = $this->faker->randomElement(self::$pairs);
        $side   = $this->faker->randomElement(['compra', 'compra', 'venda']);
        $status = $this->faker->randomElement(['executada', 'executada', 'pendente', 'cancelada']);
        $price  = $this->faker->randomFloat(2, 0.50, 350000);
        $amount = $this->faker->randomFloat(8, 0.001, 2);
        $total  = round($price * $amount, 2);

        return [
            'user_id'    => \App\Models\User::factory(),
            'pair'       => $pair,
            'side'       => $side,
            'price'      => $price,
            'amount'     => $amount,
            'total'      => $total,
            'status'     => $status,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
