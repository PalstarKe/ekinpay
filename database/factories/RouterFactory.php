<?php

namespace Database\Factories;

use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouterFactory extends Factory
{
    protected $model = Router::class;

    public function definition()
    {
        return [
            'name'           => $this->faker->word,
            'ip_address'     => $this->faker->ipv4,
            'username'       => $this->faker->userName,
            // For testing, you might want to use a known password or hash it if needed.
            'password'       => bcrypt('secret'),
            'description'    => $this->faker->sentence,
            'coordinates'    => $this->faker->latitude . ', ' . $this->faker->longitude,
            'coverage'       => $this->faker->randomDigitNotNull,
            'prefix'         => $this->faker->word,
            'enabled'        => $this->faker->numberBetween(0, 1),
            'offline_since'  => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'status'         => $this->faker->randomElement(['Online', 'Offline']),
            'last_seen'      => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'last_check'     => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
