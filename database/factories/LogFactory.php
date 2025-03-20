<?php

namespace Database\Factories;

use App\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition()
    {
        return [
            'date'        => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type'        => $this->faker->randomElement(['info', 'warning', 'error']),
            'description' => $this->faker->paragraph,
            'userid'      => $this->faker->numberBetween(1, 100),
            'ip'          => $this->faker->ipv4, // or use ipv6 if needed
        ];
    }
}
