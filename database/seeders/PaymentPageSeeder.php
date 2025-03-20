<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentPage;

class PaymentPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        PaymentPage::factory()->count(50)->create();
    }
}
