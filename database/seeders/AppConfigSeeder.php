<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppConfig;

class AppConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Example configuration data based on your provided SQL insert statements.
        $configs = [
            ['setting' => 'CompanyName', 'value' => 'THE FUTURE FIRM TELECOM'],
            ['setting' => 'currency_code', 'value' => 'Kes.'],
            ['setting' => 'language', 'value' => 'english'],
            ['setting' => 'show-logo', 'value' => '1'],
            ['setting' => 'nstyle', 'value' => 'green'],
            ['setting' => 'timezone', 'value' => 'Africa/Nairobi'],
            ['setting' => 'dec_point', 'value' => '.'],
            ['setting' => 'thousands_sep', 'value' => ','],
            ['setting' => 'rtl', 'value' => '0'],
            ['setting' => 'address', 'value' => ''],
            ['setting' => 'phone', 'value' => '+254721458919'],
            ['setting' => 'date_format', 'value' => 'd M Y'],
            // Add more entries here as needed...
        ];

        foreach ($configs as $config) {
            AppConfig::create($config);
        }
    }
}
