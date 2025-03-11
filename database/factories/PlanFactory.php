<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name_plan'      => $this->faker->word,
            // We'll output price as a string (e.g., "29.99")
            'price'          => number_format($this->faker->randomFloat(2, 5, 100), 2, '.', ''),
            'type'           => $this->faker->randomElement(['Hotspot','Fixed','Balance']),
            'typebp'         => $this->faker->randomElement(['Unlimited','Limited']),
            'limit_type'     => $this->faker->randomElement(['Time_Limit','Data_Limit','Both_Limit']),
            'time_limit'     => $this->faker->numberBetween(30, 180),
            'time_unit'      => $this->faker->randomElement(['Mins','Hrs']),
            'data_limit'     => $this->faker->numberBetween(100, 5000),
            'data_unit'      => $this->faker->randomElement(['MB','GB']),
            'validity'       => $this->faker->numberBetween(1, 365),
            'validity_unit'  => $this->faker->randomElement(['Mins','Hrs','Days','Months','Period']),
            'shared_users'   => $this->faker->numberBetween(1, 10),
            'is_radius'      => $this->faker->numberBetween(0, 1),
            'enabled'        => $this->faker->numberBetween(0, 1),
            'device'         => $this->faker->word,
            'rate_down'      => $this->faker->numberBetween(128, 1024),
            'rate_down_unit' => $this->faker->randomElement(['Kbps','Mbps']),
            'rate_up'        => $this->faker->numberBetween(128, 1024),
            'rate_up_unit'   => $this->faker->randomElement(['Kbps','Mbps']),
            'burst'          => $this->faker->word,
        ];
    }
}
