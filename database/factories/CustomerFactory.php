<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'username'         => $this->faker->userName,
            // For testing, you might want a known password (e.g., 'password')
            'password'         => Hash::make('password'),
            'photo'            => '/user.default.jpg',
            'pppoe_password'   => $this->faker->password,
            'fullname'         => $this->faker->name,
            'phonenumber'      => $this->faker->phoneNumber,
            'email'            => $this->faker->unique()->safeEmail,
            'balance'          => $this->faker->randomFloat(2, 0, 1000),
            'service_type'     => $this->faker->randomElement(['Hotspot','PPPoE','STATIC','DHCP','Others']),
            'account_type'     => $this->faker->randomElement(['Business','Personal']),
            'auto_renewal'     => $this->faker->boolean,
            'status'           => $this->faker->randomElement(['Active','Banned','Disabled','Inactive','Limited','Suspended']),
            'created_by'       => 1,
            'created_at'       => now(),
            'last_login'       => $this->faker->optional()->dateTime(),
            'account'          => $this->faker->word,
            'mac_address'      => $this->faker->macAddress,
            'static_ip'        => $this->faker->ipv4,
            'smsgroup'         => $this->faker->word,
            'charges'          => $this->faker->word,
            'package'          => $this->faker->word,
            'routers'          => $this->faker->word,
            'apartment'        => $this->faker->word,
            'location'         => $this->faker->city,
            'housenumber'      => $this->faker->buildingNumber,
            'expiry'           => $this->faker->optional()->dateTime(),
        ];
    }
}
