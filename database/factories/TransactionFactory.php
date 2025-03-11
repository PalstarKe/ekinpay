<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        // Generate a random time for recharged_time
        $rechargedTime = $this->faker->time('H:i:s', 'now');

        return [
            'invoice'         => $this->faker->numerify('INV#####'),
            'username'        => $this->faker->userName,
            'user_id'         => $this->faker->numberBetween(1, 100),
            'plan_name'       => $this->faker->word,
            'price'           => $this->faker->numerify('###.##'),
            'recharged_on'    => $this->faker->date(),
            'recharged_time'  => $rechargedTime,
            'expiration'      => $this->faker->optional()->word,
            'time'            => $this->faker->optional()->word,
            'method'          => $this->faker->randomElement(['Cash', 'Credit Card', 'Mpesa']),
            'routers'         => $this->faker->optional()->word,
            'type'            => $this->faker->randomElement(['Hotspot','PPPOE','STATIC','Balance']),
            'note'            => '',
            'admin_id'        => 1,
            'mpesacode'       => $this->faker->optional()->numerify('MPESA####'),
        ];
    }
}
