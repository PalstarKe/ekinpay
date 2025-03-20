<?php

namespace Database\Factories;

use App\Models\PaymentPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PaymentPageFactory extends Factory
{
    protected $model = PaymentPage::class;

    public function definition()
    {
        return [
            'username'            => $this->faker->userName,
            'transaction_id'      => $this->faker->optional()->uuid,
            'transaction_ref'     => $this->faker->uuid,
            'router_name'         => $this->faker->word,
            'plan_id'             => $this->faker->numberBetween(1, 50),
            'plan_name'           => $this->faker->word,
            'amount'              => $this->faker->numberBetween(100, 10000),
            'phone_number'        => $this->faker->e164PhoneNumber,
            'transaction_status'  => $this->faker->randomElement(['Success', 'Failed', 'Pending']),
            'gateway_response'    => $this->faker->optional()->paragraph,
            'payment_gateway'     => $this->faker->optional()->word,
            'payment_method'      => $this->faker->optional()->word,
            'created_date'        => $this->faker->dateTimeBetween('-1 year', 'now'),
            'payment_date'        => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'expired_date'        => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
