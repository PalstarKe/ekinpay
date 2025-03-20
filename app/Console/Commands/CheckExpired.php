<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckExpired extends Command
{
    protected $signature = 'customer:checkexpiry';
    protected $description = 'Check and update customer expiry status';

    public function handle()
    {
        Log::info('Processing customers...');
    
        $customers = Customer::where('expiry_status', 'on')->where('expiry', '<', Carbon::now())->where(function ($query) {
        $query->whereNull('expiry_extended')->orWhere('expiry_extended', '<', Carbon::now());
            })->get();

        foreach ($customers as $customer) {
            Log::info("Checking customer ID: {$customer->id}, Expiry: {$customer->expiry}, Status: {$customer->expiry_status}, Balance: {$customer->balance}");
            $balance = $customer->balance;
            $package = Package::where('name_plan', $customer->package)->where('created_by', $customer->created_by)->first();
            $packagePrice = $package->price;

            if ($balance >= $packagePrice) {
                // Renew fully
                $newExpiry = Carbon::now()->addDays(30);
                Customer::where('id', $customer->id)->update([
                    'expiry' => $newExpiry,
                    'expiry_status' => 'on',
                    'balance' => $balance - $packagePrice
                ]);

                // Update FreeRADIUS (Reactivate User)
                DB::connection('radius')->table('radcheck')
                    ->updateOrInsert(
                        ['username' => $customer->username, 'attribute' => 'Expiration'],
                        ['op' => ':=', 'value' => $newExpiry->format('d M Y')]
                    );
                
                // Assign the correct package group
                DB::connection('radius')->table('radusergroup')
                    ->updateOrInsert(
                        ['username' => $customer->username],
                        ['groupname' => $package->groupname, 'priority' => 1]
                    );

                $this->info("Customer {$customer->username} renewed until {$newExpiry}");
            } else {
                // Move to expired pool in FreeRADIUS
                DB::connection('radius')->table('radusergroup')
                    ->updateOrInsert(
                        ['username' => $customer->username],
                        ['groupname' => 'Expired_Plan', 'priority' => 10]
                    );

                $this->info("Customer {$customer->username} moved to expired pool.");
            }
        }

        return 0;
    }
}
