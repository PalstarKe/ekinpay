<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Router;

class RouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create 20 dummy router records
        Router::factory()->count(20)->create();
    }
}
