<?php

namespace Database\Factories;

use App\Models\AppConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppConfigFactory extends Factory
{
    protected $model = AppConfig::class;

    public function definition()
    {
        return [
            // You can define some common settings or generate random ones.
            // Here, we're generating a random setting name and a value.
            'setting' => $this->faker->word,
            'value'   => $this->faker->sentence,
        ];
    }
}
