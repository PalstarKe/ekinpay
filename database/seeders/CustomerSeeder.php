<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Use the Customer factory to create 50 dummy customers.
        Customer::factory()->count(50)->create();
    }
}
