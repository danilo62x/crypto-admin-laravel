<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name'       => $name,
            'email'      => $this->faker->unique()->safeEmail(),
            'password'   => 'password',
            'avatar'     => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=' . urlencode($name),
            'role'       => 'editor',
            'is_active'  => true,
            'kyc_status' => 'verificado',
        ];
    }
}
