<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'from_user' => $this->faker->userName,
            'to_user'   => $this->faker->userName,
            'title'     => $this->faker->sentence(4),
            'message'   => $this->faker->paragraph,
            // Randomly choose '0' or '1' for status
            'status'    => $this->faker->randomElement(['0', '1']),
            'date'      => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
