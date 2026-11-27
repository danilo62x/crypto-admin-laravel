<?php

namespace Database\Factories;

use App\Models\Coin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coin>
 */
class CoinFactory extends Factory
{
    protected $model = Coin::class;

    private static array $coins = [
        ['symbol' => 'BTC',  'name' => 'Bitcoin',        'price' => 320000.00],
        ['symbol' => 'ETH',  'name' => 'Ethereum',       'price' => 18500.00],
        ['symbol' => 'BNB',  'name' => 'BNB',            'price' => 2900.00],
        ['symbol' => 'USDT', 'name' => 'Tether USD',     'price' => 5.10],
        ['symbol' => 'SOL',  'name' => 'Solana',         'price' => 750.00],
        ['symbol' => 'ADA',  'name' => 'Cardano',        'price' => 2.80],
        ['symbol' => 'XRP',  'name' => 'Ripple',         'price' => 3.20],
        ['symbol' => 'DOT',  'name' => 'Polkadot',       'price' => 38.00],
        ['symbol' => 'DOGE', 'name' => 'Dogecoin',       'price' => 0.85],
        ['symbol' => 'AVAX', 'name' => 'Avalanche',      'price' => 185.00],
        ['symbol' => 'MATIC','name' => 'Polygon',        'price' => 3.50],
        ['symbol' => 'LTC',  'name' => 'Litecoin',       'price' => 520.00],
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $coin = self::$coins[self::$index % count(self::$coins)];
        self::$index++;

        $variation = $this->faker->randomFloat(4, 0.85, 1.15);

        return [
            'symbol'      => $coin['symbol'],
            'name'        => $coin['name'],
            'price'       => round($coin['price'] * $variation, 8),
            'description' => '<p>' . $this->faker->sentence(12) . '</p>',
            'logo_url'    => 'https://ui-avatars.com/api/?background=f59e0b&color=fff&bold=true&name=' . $coin['symbol'],
            'is_active'   => $this->faker->boolean(85),
        ];
    }
}
