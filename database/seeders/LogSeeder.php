<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Log;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create 50 dummy log records using the factory.
        Log::factory()->count(50)->create();
    }
}
