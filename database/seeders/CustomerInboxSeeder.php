<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerInbox;

class CustomerInboxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create 50 dummy records
        CustomerInbox::factory()->count(50)->create();
    }
}
