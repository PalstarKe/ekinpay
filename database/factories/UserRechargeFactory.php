<?php

namespace Database\Factories;

use App\Models\UserRecharge;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRechargeFactory extends Factory
{
    protected $model = UserRecharge::class;

    public function definition()
    {
        return [
            'customer_id'     => $this->faker->numberBetween(1, 100),
            'username'        => $this->faker->userName,
            'plan_id'         => $this->faker->numberBetween(1, 50),
            'namebp'          => $this->faker->word,
            'recharged_on'    => $this->faker->date(),
            'recharged_time'  => $this->faker->time('H:i:s', 'now'),
            'expiration'      => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'time'            => $this->faker->time('H:i:s'),
            'status'          => $this->faker->randomElement(['Active', 'Expired', 'Pending']),
            'method'          => $this->faker->randomElement(['Mpesa', 'Cash', 'Credit Card']),
            'routers'         => $this->faker->word,
            'type'            => $this->faker->randomElement(['Hotspot', 'PPPOE', 'STATIC', 'Balance']),
            'admin_id'        => 1,
            'mpesacode'       => $this->faker->optional()->numerify('MPESA####'),
            'account'         => $this->faker->optional()->word,
        ];
    }
}
