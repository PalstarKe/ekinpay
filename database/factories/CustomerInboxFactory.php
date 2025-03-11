<?php

namespace Database\Factories;

use App\Models\CustomerInbox;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerInboxFactory extends Factory
{
    protected $model = CustomerInbox::class;

    public function definition()
    {
        // Create a random date for creation and optionally for read date
        $dateCreated = $this->faker->dateTimeBetween('-1 year', 'now');
        $dateRead = $this->faker->optional(0.5)->dateTimeBetween($dateCreated, 'now');

        return [
            'customer_id'  => $this->faker->numberBetween(1, 100), // Adjust range as needed
            'date_created' => $dateCreated,
            'date_read'    => $dateRead,
            'subject'      => $this->faker->sentence(3), // A short subject
            'body'         => $this->faker->paragraph,
            'from'         => $this->faker->randomElement(['System', 'Admin', 'Other']),
            'admin_id'     => $this->faker->numberBetween(0, 10),
        ];
    }
}
