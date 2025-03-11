<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition()
    {
        // For created_date we use now() and optionally set paid_date after that
        $createdDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $paidDate = $this->faker->optional()->dateTimeBetween($createdDate, 'now');

        return [
            'username'           => $this->faker->userName,
            'user_id'            => $this->faker->numberBetween(1, 100),
            'gateway'            => $this->faker->randomElement(['xendit', 'midtrans']),
            'checkout'           => $this->faker->word,
            'gateway_trx_id'     => $this->faker->uuid,
            'plan_id'            => $this->faker->numberBetween(1, 50),
            'plan_name'          => $this->faker->word,
            'routers_id'         => $this->faker->numberBetween(1, 10),
            'routers'            => $this->faker->word,
            'price'              => $this->faker->randomFloat(2, 10, 1000),
            'pg_url_payment'     => $this->faker->url,
            'payment_method'     => $this->faker->randomElement(['credit_card', 'bank_transfer', 'virtual_account']),
            'payment_channel'    => $this->faker->randomElement(['online', 'offline']),
            'pg_request'         => $this->faker->optional()->paragraph,
            'pg_paid_response'   => $this->faker->optional()->paragraph,
            'expired_date'       => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'created_date'       => $createdDate,
            'paid_date'          => $paidDate,
            'trx_invoice'        => $this->faker->numerify('INV#####'),
            'status'             => $this->faker->randomElement([1, 2, 3, 4]),
        ];
    }
}
