<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run()
    {
        // Create 50 dummy plans
        Plan::factory()->count(50)->create();
    }
}
