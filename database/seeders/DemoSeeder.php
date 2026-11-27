<?php

namespace Database\Seeders;

use App\Models\Coin;
use App\Models\ExchangeOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Users com kyc_status variado
        $users = User::factory()->count(20)->create([
            'kyc_status' => fn () => fake()->randomElement(['pendente', 'verificado', 'verificado', 'verificado', 'recusado']),
        ]);

        // Moedas fixas para ter dados coerentes
        $coins = Coin::factory()->count(12)->create();

        // Wallets: cada usuário ganha 1-3 carteiras em moedas diferentes
        $wallets = collect();
        foreach ($users as $user) {
            $userCoins = $coins->random(rand(1, 3));
            foreach ($userCoins as $coin) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'coin_id' => $coin->id,
                    'balance' => fake()->randomFloat(8, 0.001, 8),
                    'address' => '0x' . strtolower(Str::random(40)),
                ]);
                $wallets->push($wallet);
            }
        }

        // Transações: 4-8 por wallet
        foreach ($wallets as $wallet) {
            $count = rand(4, 8);
            for ($i = 0; $i < $count; $i++) {
                $type   = fake()->randomElement(['deposito', 'deposito', 'saque', 'trade']);
                $status = fake()->randomElement(['concluida', 'concluida', 'concluida', 'pendente', 'falha']);
                $amount = fake()->randomFloat(8, 0.0001, 3);

                Transaction::create([
                    'wallet_id'    => $wallet->id,
                    'type'         => $type,
                    'amount'       => $amount,
                    'fee'          => round($amount * 0.001, 8),
                    'status'       => $status,
                    'tx_hash'      => '0x' . strtolower(Str::random(64)),
                    'notes'        => fake()->boolean(25) ? fake()->sentence() : null,
                    'confirmed_at' => $status === 'concluida'
                        ? fake()->dateTimeBetween('-6 months', 'now')
                        : null,
                    'created_at'   => fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Exchange Orders: 2-5 por usuário
        $pairs = ['BTC/BRL', 'ETH/BRL', 'SOL/BRL', 'BNB/BRL', 'ADA/BRL', 'XRP/BRL'];
        foreach ($users as $user) {
            $count = rand(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $price  = fake()->randomFloat(2, 0.50, 350000);
                $amount = fake()->randomFloat(8, 0.001, 2);

                ExchangeOrder::create([
                    'user_id'    => $user->id,
                    'pair'       => fake()->randomElement($pairs),
                    'side'       => fake()->randomElement(['compra', 'compra', 'venda']),
                    'price'      => $price,
                    'amount'     => $amount,
                    'total'      => round($price * $amount, 2),
                    'status'     => fake()->randomElement(['executada', 'executada', 'pendente', 'cancelada']),
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
