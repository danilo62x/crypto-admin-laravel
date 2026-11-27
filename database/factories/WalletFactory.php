<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'coin_id' => \App\Models\Coin::factory(),
            'balance' => $this->faker->randomFloat(8, 0, 10),
            'address' => '0x' . strtolower(Str::random(40)),
        ];
    }
}
