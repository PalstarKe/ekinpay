<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Bandwidth;
use App\Models\Utility;
use App\Models\User;
use App\Models\Nas;
use App\Models\Router;
use App\Models\RouterPackage;
use App\Models\Plan;
use App\Models\Customer;
use Auth;
use Carbon\Carbon;
use App\Helpers\CustomHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class CaptivePortalController extends Controller
{

    public function showLogin(Request $request, $nas_ip, $mac = null)
    {
        Log::info('Incoming Request:', $request->all());
    
        if (!$nas_ip) {
            return abort(400, 'NAS IP is required');
        }
    
        $router = Router::where('ip_address', $nas_ip)->first();
        if (!$router) {
            return abort(404, 'Router not found');
        }
        $createdby = $router->created_by;

        $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
        $packages = Package::with('bandwidth')
            ->whereIn('id', $packageIds)
            ->where('created_by', $createdby)
            ->where('type', 'Hotspot')
            ->get();
    
        $company = User::find($createdby);

            // ✅ Store values from the request OR session (NO FALLBACK)
            if ($request->query('loginLink')) {
                session(['hotspot_login.loginLink' => $request->query('loginLink')]);
            }
            $loginLink = session('hotspot_login.loginLink');

            Log::info('Stored Login Link:', ['loginLink' => $loginLink]);

    if (!$loginLink) {
        Log::error('Missing loginLink');
        return abort(400, 'Missing loginLink');
    }
        $chapID = $request->query('chapID') ?? session('hotspot_login.chapID');
        $chapChallenge = $request->query('chapChallenge') ?? session('hotspot_login.chapChallenge');

        // Ensure loginLink is present, otherwise abort
        if (!$loginLink) {
            Log::error('Missing loginLink');
            return abort(400, 'Missing loginLink');
        }
        $mac = $mac ?? $request->query('mac') ?? $request->input('mac');
        Log::info('Resolved MAC Address:', ['mac' => $mac]);
    
        $invalidMacs = ['$(mac)', null, '', 'undefined'];
        if (in_array($mac, $invalidMacs, true)) {
            Log::error('Invalid MAC address detected', ['mac' => $mac]);
            return abort(403, 'Invalid MAC address. Please try again.');
        }
    
        if ($request->getQueryString()) {
            return redirect()->route('captive.showLogin', ['nas_ip' => $nas_ip, 'mac' => $mac]);
        }
        
    
        session([
            'hotspot_login' => [
                'nas_ip' => $nas_ip,
                'mac' => $mac,
                'ip' => $request->query('ip'),
                'loginLink' => $loginLink,
                'chapID' => $chapID,
                'chapChallenge' => $chapChallenge,
            ],
            'packages' => $packages,
            'company' => $company
        ]);
    
        return view('captive.login', compact('nas_ip', 'packages', 'company', 'mac', 'loginLink', 'chapID', 'chapChallenge'));
    }
    
    public function processCustomer(Request $request)
    {
        // Validate request data
        $rules = [
            'nas_ip'      => 'required',
            'package_id'  => 'required',
            'phone_number'=> 'required',
            'mac_address' => 'required'
        ];
    
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        // Get router details
        $router = Router::where('ip_address', $request->nas_ip)->first();
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'NAS not found']);
        }

        // Get package details
        $package = Package::with('bandwidth')
            ->where('id', $request->package_id)
            ->where('created_by', $router->created_by)
            ->where('type', 'Hotspot')
            ->first();

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found']);
        }

        // Get ISP (Created By)
        $user = User::find($router->created_by);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'ISP not found']);
        }

        // Get plan details
        $creator = User::find($user->creatorId());
        $plan = Plan::find($creator->plan);

        // Check customer limit
        $totalCustomers = $user->countCustomers();
        if ($totalCustomers >= $plan->max_customers && $plan->max_customers != -1) {
            return response()->json(['success' => false, 'message' => 'Customer limit reached']);
        }

        $phone= $request->phone_number;
        $phone = (substr($phone, 0,1) == '+') ? str_replace('+', '', $phone) : $phone;
        $phone = (substr($phone, 0,1) == '0') ? preg_replace('/^0/', '254', $phone) : $phone;
        $phone = (substr($phone, 0,1) == '7') ? preg_replace('/^7/', '2547', $phone) : $phone;
        $phone = (substr($phone, 0,1) == '1') ? preg_replace('/^1/', '2541', $phone) : $phone;
        $phone = (substr($phone, 0,1) == '0') ? preg_replace('/^01/', '2541', $phone) : $phone;
        $phone = (substr($phone, 0,1) == '0') ? preg_replace('/^07/', '2547', $phone) : $phone;
    
        $customer = Customer::where('fullname', $phone)
        ->where('mac_address', $request->mac_address)
        ->first();

        // Create new customer
        if (!$customer) {
            $customer = new Customer();
            $customer->fullname     = $phone;
            $customer->username     = $request->mac_address;
            $customer->account      = $request->mac_address;
            $customer->password     = $request->mac_address;
            $customer->contact      = $phone;
            $customer->created_by   = $router->created_by;
            $customer->service      = 'Hotspot';
            $customer->auto_renewal = 1;
            $customer->is_active    = 1;
            $customer->mac_address  = $request->mac_address;
            $customer->package      = $package->name_plan;
            $customer->save();
        }
        $cID = $customer->id;

        $mpesaResponse = CustomHelper::initiateHotspotSTKPush($phone, $package->price, $router->created_by);

        $mpesaResponse = (array) $mpesaResponse; 

        $checkoutRequestID = $mpesaResponse['CheckoutRequestID'] ?? null;

        Log::info("Response for Checkout:", ['CheckoutRequestID' => $checkoutRequestID]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent',
            'checkoutRequestID' => $checkoutRequestID,
            'cID' => $cID
        ]);

    }

    public function processQueryMpesa(Request $request)
    {
        $rules = [
            'ref'          => 'required',
            'nas_ip'       => 'required',
            'package_id'   => 'required',
            'phone_number' => 'required',
            'mac_address'  => 'required',
            'cID'          => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $router = Router::where('ip_address', $request->nas_ip)->first();
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'NAS not found']);
        }

        $ref = $request->ref;
        
        // Normalize phone number
        $phone = $request->phone_number;
        $phone = ltrim($phone, '+'); // Remove +
        $phone = preg_replace('/^0/', '254', $phone); // Convert 07xxxxxxx -> 2547xxxxxxx, 01xxxxxxx -> 2541xxxxxxx
        
        // Get customer
        $customer = Customer::find($request->cID);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found']);
        }

        // Query M-Pesa transaction status
        $mpesastatus = CustomHelper::QueryMpesaHotspot($ref, $router->created_by);
        $mpesastatus = (array) $mpesastatus; // Ensure it's an array
        Log::info("Response for STK in captive:", (array)  $mpesastatus);

        $ResultCode = $mpesastatus['ResultCode'] ?? null;
        $ResultDesc = $mpesastatus['ResultDesc'] ?? null;

        if ($ResultCode !== "0") {
            return response()->json(['success' => false, 'message' => 'Payment failed', 'ResultCode' => $ResultCode, 'ResultDesc' => $ResultDesc]);
        }

        // Get package details
        $package = Package::with('bandwidth')
            ->where('id', $request->package_id)
            ->where('created_by', $router->created_by)
            ->where('type', 'Hotspot')
            ->first();

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found']);
        }

        // Convert validity to seconds
        $timelimit = match ($package->validity_unit) {
            'Minutes' => $package->validity * 60,
            'Hours'   => $package->validity * 3600,
            'Days'    => $package->validity * 86400,
            'Months'  => $package->validity * 2592000,
            default   => 0
        };

        // Set new expiry date
        $expiry = Carbon::now()->addSeconds($timelimit);

        // Update customer expiry
        $customer->expiry = $expiry->toDateTimeString();
        $customer->expiry_status = 'on';
        $customer->save();

        // Ensure radcheck entry exists (no duplicate passwords)
        $existingRadcheck = DB::table('radcheck')
        ->where('username', $customer->username)
        ->where('attribute', 'Cleartext-Password')
        ->exists();

        if (!$existingRadcheck) {
        DB::table('radcheck')->insert([
            'username'   => $customer->username,
            'attribute'  => 'Cleartext-Password',
            'op'         => ':=',
            'value'      => $customer->password,
            'created_by' => $router->created_by,
        ]);
        }

        $group_name = 'package_' . $package->id;

        // Check existing package assignment
        $existingRadusergroup = DB::table('radusergroup')
        ->where('username', $customer->username)
        ->first();

        if ($existingRadusergroup) {
        if ($existingRadusergroup->groupname !== $group_name) {
            DB::table('radusergroup')
                ->where('username', $customer->username)
                ->delete();
        }
        }

        // Assign new package
        DB::table('radusergroup')->insert([
        'username'   => $customer->username,
        'groupname'  => $group_name,
        'priority'   => 1,
        'created_by' => $router->created_by,
        ]);

        // Extend or reset expiry based on the new package
        $currentExpiry = Carbon::parse($customer->expiry);
        if ($customer->expiry_status === 'off' || Carbon::now()->gt($currentExpiry)) {
        // If expired, start new expiry from now
        $newExpiry = Carbon::now()->addSeconds($timelimit);
        } else {
        // If active, extend from the current expiry
        $newExpiry = $currentExpiry->addSeconds($timelimit);
        }

        // Update customer expiry in your system
        $customer->expiry = $newExpiry->toDateTimeString();
        $customer->expiry_status = 'on';
        $customer->save();


        $type = 'package';

        // Generate and process invoice
        $invoice = CustomHelper::generateInvoiceH($customer, $type, $package->price);
        $invoicePayment = CustomHelper::recordInvoicePaymentH($customer, $invoice, $package->price);
        CustomHelper::updateInvoiceStatusH($invoice);
        $invoicePayment->refresh();
        
        // Process transaction
        CustomHelper::processTransactionH($invoicePayment, $invoice->customer_id, $router->created_by);
        
        // Send notification
        // CustomHelper::sendInvoiceNotificationH($customer, $invoice, $package->price);

        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'ResultCode' => $ResultCode,
            'ResultDesc' => $ResultDesc
        ]);
    }
    public function connect(Request $request)
    {
        // Retrieve session data
        $mac = Session::get('mac');
        $ip = Session::get('ip');
        $link_login_only = Session::get('link_login_only');
        $linkorig = 'https://www.google.com'; // Default redirect

        $username = "admin"; // You may replace this with dynamic authentication

        return view('captive.connect', compact('username', 'link_login_only', 'linkorig'));
    }
    public function VerifyMpesa($nas_ip = null){

    }
    public function reedemVoucher($nas_ip = null){

    }
    public function mpesaCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received:', $request->all());

        // Process M-Pesa response
        $mpesaData = $request->all();
        
        // Check if the transaction was successful
        if (isset($mpesaData['Body']['stkCallback']['ResultCode']) && $mpesaData['Body']['stkCallback']['ResultCode'] == 0) {
            $amount = $mpesaData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] ?? null;
            $mpesaReceipt = $mpesaData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] ?? null;
            $phoneNumber = $mpesaData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'] ?? null;

            // Store payment in the database (you may adjust this based on your payment model)
            \DB::table('payments')->insert([
                'phone_number' => $phoneNumber,
                'amount'       => $amount,
                'mpesa_receipt'=> $mpesaReceipt,
                'status'       => 'success',
                'created_at'   => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment processed successfully']);
        } 

        return response()->json(['success' => false, 'message' => 'Payment failed']);
    }
}
