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

    public static function sendTelegram($txt)
    {
        $bot = config('services.telegram.bot');
        $chatId = config('services.telegram.target_id');

        if (!empty($bot) && !empty($chatId)) {
            return Http::get("https://api.telegram.org/bot{$bot}/sendMessage", [
                'query' => [
                    'chat_id' => $chatId,
                    'text' => $txt
                ]
            ]);
        }
    }

    public static function sendWhatsapp($phone, $txt)
    {
        if (empty($txt)) {
            return "";
        }
        // Notification Handling
        $settings = Utility::settings(\Auth::user()->creatorId());
        // if (!empty($settings['twilio_customer_notification']) && $settings['twilio_customer_notification'] == 1) {
        //     Utility::send_twilio_msg($request->contact, 'new_customer', [
        //         'user_name'      => \Auth::user()->name,
        //         'customer_name'  => $customer->fullname,
        //         'customer_email' => $customer->email,
        //     ]);
        // }

        $waUrl = config('services.whatsapp.url');

        if (!empty($waUrl)) {
            $waUrl = str_replace(['[number]', '[text]'], [urlencode($phone), urlencode($txt)], $waUrl);
            return Http::get($waUrl);
        }
    }
    
    public static function sendSMS($phone, $txt)
    {
        if (empty($txt)) {
            return "";
        }

        $smsUrl = config('services.sms.url');

        if (!empty($smsUrl)) {
                $smsUrl = str_replace(['[number]', '[text]'], [urlencode($phone), urlencode($txt)], $smsUrl);
                return Http::get($smsUrl);
        }
        
    }
    public static function getHotspotPaymentGateway($user_id)
    {
        $companySettings = \DB::table('company_payment_settings')->where('created_by', $user_id)->pluck('value', 'name')->toArray();
    
        $adminSettings = \DB::table('admin_payment_settings')->pluck('value', 'name')->toArray();
    
        $modes = [
            'mpesa'   => $companySettings['mpesa_mode'] ?? null,
            'paybill' => $companySettings['mpesa_paybill_mode'] ?? null,
            'till'    => $companySettings['mpesa_till_mode'] ?? null,
            'bank'    => $companySettings['mpesa_bank_mode'] ?? null,
        ];
    
        $selectedMode = null;
        foreach ($modes as $mode => $value) {
            if (!empty($value) && in_array($value, ['both', 'Hotspot'])) {
                $selectedMode = $mode;
                break;
            }
        }

        if (!$selectedMode) {
            return ['success' => false, 'message' => 'No valid payment gateway found.'];
        }
    
        $isSystemAPIEnabled = false;
        if ($selectedMode === 'bank') {
            $isSystemAPIEnabled = $companySettings['is_system_mpesa_bank_api_enabled'] ?? 'off';
        } elseif ($selectedMode === 'paybill') {
            $isSystemAPIEnabled = $companySettings['is_system_mpesa_paybill_api_enabled'] ?? 'off';
        } elseif ($selectedMode === 'till') {
            $isSystemAPIEnabled = $companySettings['is_system_mpesa_till_api_enabled'] ?? 'off';
        }

        $paymentDetails = [
            'partyB' => $selectedMode === 'bank' ? $companySettings['mpesa_bank_paybill'] ?? null :
                        ($selectedMode === 'paybill' ? $companySettings['mpesa_paybill'] ?? null : 
                        ($selectedMode === 'till' ? $companySettings['mpesa_till'] ?? null : null)),
            'ref'    => $selectedMode === 'bank' ? $companySettings['mpesa_bank_account'] ?? null :
                        ($selectedMode === 'paybill' ? $companySettings['mpesa_paybill_account'] ?? null : 
                        ($selectedMode === 'till' ? $companySettings['mpesa_till_account'] ?? null : null))
        ];
    
        if ($isSystemAPIEnabled === 'on') {
            if ($selectedMode === 'till') {
                $paymentDetails['key']       = $adminSettings['personal_till_key'] ?? null;
                $paymentDetails['secret']    = $adminSettings['personal_till_secret'] ?? null;
                $paymentDetails['shortcode'] = $adminSettings['personal_till_shortcode'] ?? null;
                $paymentDetails['passkey']   = $adminSettings['personal_till_passkey'] ?? null;
                $paymentDetails['TransType']   = 'CustomerBuyGoodsOnline';
            } elseif (in_array($selectedMode, ['paybill', 'bank'])) {
                $paymentDetails['key']       = $adminSettings['personal_paybill_key'] ?? null;
                $paymentDetails['secret']    = $adminSettings['personal_paybill_secret'] ?? null;
                $paymentDetails['shortcode'] = $adminSettings['personal_paybill_shortcode'] ?? null;
                $paymentDetails['passkey']   = $adminSettings['personal_paybill_passkey'] ?? null;
                $paymentDetails['TransType']   = 'CustomerPayBillOnline';
            }
            return $paymentDetails;
        }
    
        // Validate shortcode type if API is OFF
        $shortcodeType = $companySettings['mpesa_shortcode_type'] ?? null;
    
        if ($selectedMode === 'till' && $shortcodeType === 'paybill') {
            return ['success' => false, 'message' => 'Invalid: Till cannot use Paybill shortcode type.'];
        }
        if (in_array($selectedMode, ['bank', 'paybill']) && $shortcodeType === 'till') {
            return ['success' => false, 'message' => 'Invalid: Bank/Paybill cannot use Till shortcode type.'];
        }
    
        // Use company settings for API credentials if API is OFF
        $paymentDetails['key']       = $companySettings['mpesa_key'] ?? null;
        $paymentDetails['secret']    = $companySettings['mpesa_secret'] ?? null;
        $paymentDetails['shortcode'] = $companySettings['mpesa_shortcode'] ?? null;
        $paymentDetails['passkey']   = $companySettings['mpesa_passkey'] ?? null;
        if ($selectedMode === 'till') {
            $paymentDetails['TransType']   = 'CustomerBuyGoodsOnline';
        } elseif (in_array($selectedMode, ['paybill', 'bank'])) {
            $paymentDetails['TransType']   = 'CustomerPayBillOnline';
        }
    
        return $paymentDetails;
    }
    


    public static function initiateHotspotSTKPush($phone, $amount, $isp)
    {
        $paymentSettings = self::getHotspotPaymentGateway($isp);

        if (!isset($paymentSettings['partyB']) || !isset($paymentSettings['ref'])) {
            return ['success' => false, 'message' => 'Payment settings not found.'];
        }

        // Extract required credentials
        $accRef          = $paymentSettings['ref'] ?? null;
        $PartyB          = $paymentSettings['partyB'] ?? null;
        $TransType       = $paymentSettings['TransType'] ?? null;
        $shortcode       = $paymentSettings['shortcode'] ?? null;
        $passkey         = $paymentSettings['passkey'] ?? null;
        $consumerKey     = $paymentSettings['key'] ?? null;
        $consumerSecret  = $paymentSettings['secret'] ?? null;
        $callbackUrl     = route('mpesaCallback'); 

        // Validate essential credentials
        if (!$shortcode || !$passkey || !$consumerKey || !$consumerSecret) {
            return ['success' => false, 'message' => 'Incomplete payment credentials.'];
        }

        // Prepare STK push request
        $Timestamp = date("YmdHis",time());
        $password  = base64_encode($shortcode . $passkey . $timestamp);

        $stkPushRequest = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => $TransType,
            'Amount'            => $amount,
            'PartyA'            => $phone,
            'PartyB'            => $PartyB,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => $accRef,
            'TransactionDesc'   => 'Hot Payment'
        ];

        $response = self::sendMpesaSTKPush($stkPushRequest, $consumerKey, $consumerSecret);

        return $response;
    }

    public static function sendMpesaSTKPush($stkPushRequest, $consumerKey, $consumerSecret)
    {
        $accessToken = self::getMpesaAccessToken($consumerKey, $consumerSecret);
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to obtain M-Pesa access token.'];
        }

        $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPushRequest));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public static function getMpesaAccessToken($consumerKey, $consumerSecret)
    {
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $headers = ['Authorization: Basic ' . $credentials];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }

}
    