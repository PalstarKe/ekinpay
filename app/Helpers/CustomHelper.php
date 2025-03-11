<?php

namespace App\Helpers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
    
class CustomHelper
{
    public static function lockMac(Customer $customer)
    {
        if (empty($customer->mac_address)) {
            $mac = DB::connection('radius')
                ->table('radacct')
                ->whereNull('acctstoptime')
                ->where('username', $customer->username)
                ->value('callingstationid');

            if (!empty($mac)) {
                $customer->mac_address = $mac;
                $customer->save();

                // Only insert into radcheck when first locking
                DB::connection('radius')->table('radcheck')->updateOrInsert(
                    ['username' => $customer->username, 'attribute' => 'Calling-Station-Id'],
                    ['op' => '==', 'value' => $customer->mac_address]
                );
            }
        }
    }
    public static function updatePlan($customer)
    {
        $package = Package::where('name_plan', $customer->package)->firstOrFail();
        $group_name = 'package_' . $package->id;

        if (!empty($group_name)) {
            DB::transaction(function () use ($customer, $group_name) {
                DB::connection('radius')->table('radusergroup')->where('username', $customer->username)->delete();
                DB::connection('radius')->table('radusergroup')->insert([
                    'username'  => $customer->username,
                    'groupname' => $group_name,
                    'priority'  => 1,
                ]);
            });

            // Check if client is active
            $active = DB::connection('radius')->table('radacct')->where('username', $customer->username)->whereNull('acctstoptime')->orderBy('acctstarttime', 'desc')->first();

            if (!empty($active)) {
                $nasObj = DB::connection('radius')->table('nas')->where('nasname', $active->nasipaddress)->first();

                if ($nasObj) {
                    $attributes = [
                        'acctSessionID' => $active->acctsessionid,
                        'framedIPAddress' => $active->framedipaddress,
                    ];

                    if($customer->is_active == 1){
                    // Format CoA data (download/upload speed)
                        $downm = $package->bandwidth->rate_down . $package->bandwidth->rate_down_unit;
                        $upm = $package->bandwidth->rate_up . $package->bandwidth->rate_up_unit;
                        $CoAData = $downm . "/" . $upm;
                    } else{
                        // Inactive or expired customer, use default rate (256K/256K)
                        $CoAData = "256K/256K";
                    }

                    self::sendCoA($nasObj, $customer, $attributes, $CoAData);
                } else {
                    Log::error("NAS not found for active session: " . json_encode($active));
                }
            }
        }
    }
    public static function refreshCustomerInRadius($customer)
    {
        // Get active session
        $activeSession = DB::connection('radius')->table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->first();

        if (!$activeSession) {
            return ['status' => 'error', 'message' => 'User is not online'];
        }

        $nasObj = DB::connection('radius')->table('nas')
            ->where('nasname', $activeSession->nasipaddress)
            ->first();

        if (!$nasObj) {
            return ['status' => 'error', 'message' => 'NAS not found'];
        }

        $attributes = [
            'acctSessionID'   => $activeSession->acctsessionid,
            'framedIPAddress' => $activeSession->framedipaddress,
        ];

        // Get customer's assigned package
        $package = Package::where('name_plan', $customer->package)->first();
        if (!$package) {
            return ['status' => 'error', 'message' => 'Package not found'];
        }
        if($customer->is_active == 1){
        // Format CoA data (download/upload speed)
            $downm = $package->bandwidth->rate_down . $package->bandwidth->rate_down_unit;
            $upm = $package->bandwidth->rate_up . $package->bandwidth->rate_up_unit;
            $CoAData = $downm . "/" . $upm;
        } else{
            // Inactive or expired customer, use default rate (256K/256K)
            $CoAData = "256K/256K";
        }

        // Send CoA request (assuming you have sendCoA in your helper)
        
        self::sendCoA($nasObj, $customer, $attributes, $CoAData);

        return ['status' => 'success', 'message' => 'CoA sent successfully'];
    }

    public static function sendCoA($nasObj, $userData, array $attributes, $CoAData)
    {
        if (!isset($attributes['acctSessionID'], $attributes['framedIPAddress'])) {
            Log::error("Missing required attributes for CoA: " . json_encode($attributes));
            return false;
        }

        $username = $userData->username;
        $nasname = $nasObj->nasname;
        $nasport = $nasObj->incoming_port ?? 3799;
        $nassecret = $nasObj->secret;

        $acctSessionID = escapeshellarg($attributes['acctSessionID']);
        $framedIPAddress = escapeshellarg($attributes['framedIPAddress']);
        $rateLimit = escapeshellarg($CoAData);

        $command = "echo \"User-Name=$username, Acct-Session-Id=$acctSessionID, Framed-IP-Address=$framedIPAddress, Mikrotik-Rate-Limit=$rateLimit\" | radclient -x $nasname:$nasport coa $nassecret";

        $response = shell_exec($command);
        Log::info("CoA Response for $username: " . $response);

        return strpos($response, 'Received CoA-ACK') !== false;
    }

    public static function handleDeactivation($customer)
    {
        $activeSession = DB::connection('radius')->table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->first();

        if ($activeSession) {
            $nasObj = DB::connection('radius')->table('nas')->where('nasname', $activeSession->nasipaddress)->first();
            $attributes = [
                'acctSessionID' => $activeSession->acctsessionid,
                'framedIPAddress' => $activeSession->framedipaddress,
            ];

            self::kickOutUsersByRadius($nasObj, $customer, $attributes);
        }

        DB::connection('radius')->table('radusergroup')->where('username', $customer->username)->delete();
        DB::connection('radius')->table('radusergroup')->insert([
            'username'  => $customer->username,
            'groupname' => 'Expired_Plan',
            'priority'  => 1,
        ]);
    }

    public static function kickOutUsersByRadius($nasObj, $userData, array $attributes)
    {
        $username = $userData->username;
        $nasport = $nasObj->incoming_port ?? 3799;
        $nassecret = $nasObj->secret;
        $nasname = $nasObj->nasname;
        $command = 'disconnect';

        if (!isset($attributes['acctSessionID'], $attributes['framedIPAddress'])) {
            Log::error("Missing required attributes for Disconnect: " . json_encode($attributes));
            return false;
        }

        $args = escapeshellarg("$nasname:$nasport") . ' ' . escapeshellarg($command) . ' ' . escapeshellarg($nassecret);
        $query = 'User-Name=' . escapeshellarg($username) . 
                ',Acct-Session-Id=' . escapeshellarg($attributes['acctSessionID']) . 
                ',Framed-IP-Address=' . escapeshellarg($attributes['framedIPAddress']);

        $cmd = 'echo ' . escapeshellarg($query) . ' | radclient -xr 1 ' . $args . ' 2>&1';

        $res = shell_exec($cmd);
        Log::info("Disconnect response for $username: " . $res);

        return (strpos($res, 'Received Disconnect-ACK') !== false);
    }

    public static function assignCustomerPackage($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);
        $package = Package::where('name_plan', $customer->package)->firstOrFail(); 
        $group_name = 'package_' . $package->id;

        $existingAssignment = DB::connection('radius')->table('radusergroup')->where('username', $customer->username)->exists();

        if (!$existingAssignment) {
            DB::connection('radius')->table('radusergroup')->insert([
                'username' => $customer->username,
                'groupname' => $group_name,
                'priority' => 0
            ]);
        } else {
            DB::connection('radius')->table('radusergroup')
                ->where('username', $customer->username)
                ->update(['groupname' => $group_name]);
        }

        $expirationValue = $customer->expiry_extended ? $customer->expiry_extended->format('Y-m-d H:i:s') : Carbon::now()->addDays(30)->format('Y-m-d H:i:s');

        $expirationExists = DB::connection('radius')->table('radcheck')->where('username', $customer->username)->where('attribute', 'Expiration')->exists();

        if (!$expirationExists) {
            DB::connection('radius')->table('radcheck')->insert([
                'username' => $customer->username,
                'attribute' => 'Expiration',
                'op' => ':=',
                'value' => $expirationValue
            ]);
        } else {
            DB::connection('radius')->table('radcheck')->where('username', $customer->username)->where('attribute', 'Expiration')->update(['value' => $expirationValue]);
        }
    }
    public static function getMacVendor($mac)
    {
        $mac = strtoupper($mac);
        $apiUrl = "https://api.macvendors.com/{$mac}";

        try {
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                return $response->body(); // Vendor name
            }

            return "Unknown Device";
        } catch (\Exception $e) {
            return "Unknown Device"; // In case of failure
        }
    }
    
    public static function generateInvoice($customer, $type, $amount)
    {
        return Invoice::create([
            'invoice_id' => self::invoiceNumber(),
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now(),
            'send_date' => now(),
            'ref_number' => Auth::user()->invoiceNumberFormat(self::invoiceNumber()),
            'status' => 'Unpaid',
            'category' => $type,
            'created_by' => auth()->id(),
        ]);
    }

    public static function recordInvoicePayment($customer, $invoice, $amount)
    {
        return InvoicePayment::create([
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'payment_method' => 'Balance',
            'date' => now(),
            'created_by' => auth()->id(),
        ]);
    }

    public static function updateInvoiceStatus($invoice)
    {
        if ($invoice->getDue() <= 0) {
            $invoice->status = 'Paid';
            $invoice->save();
        }
    }

    public static function processTransaction($invoicePayment, $user_id)
    {
        $invoicePayment->user_id = $user_id;
        $invoicePayment->user_type = 'Customer';
        $invoicePayment->type = 'Partial';
        $invoicePayment->created_by = auth()->id();
        $invoicePayment->payment_id = $invoicePayment->id;
        $invoicePayment->category = 'Invoice';

        Transaction::addTransaction($invoicePayment);
    }

    public static function sendInvoiceNotification($customer, $invoice, $amount)
    {
        $settings = Utility::settings();
        if ($settings['new_invoice_payment'] == 1) {
            $invoicePaymentArr = [
                'invoice_payment_name' => $customer->name,
                'invoice_payment_amount' => $amount,
                'invoice_payment_date' => now()->format('Y-m-d'),
                'payment_dueAmount' => $invoice->getDue(),
                'invoice_number' => Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                'invoice_payment_method' => 'Balance',
            ];

            Utility::sendEmailTemplate('new_invoice_payment', [$customer->id => $customer->email], $invoicePaymentArr);
        }
    }

    public static function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }
}
    