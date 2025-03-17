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
    // public function showLogin($nas_ip = null)
    // {
    //     $router = Router::where('ip_address', $nas_ip)->first();
    
    //     if (!$router) {
    //         return abort(404, 'Router not found');
    //     }
    
    //     // Get package IDs assigned to the router
    //     $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
    
    //     if ($packageIds->isNotEmpty()) {
    //         $packages = Package::with('bandwidth')
    //             ->whereIn('id', $packageIds)
    //             ->where('created_by', $router->created_by)
    //             ->where('type', 'Hotspot')
    //             ->get();
    //     } else {
    //         $packages = collect();
    //     }
    
    //     return view('captive.login', compact('nas_ip', 'packages'));
    // }
    // public function showLogin($nas_ip, Request $request)
    // {
    //     $router = Router::where('ip_address', $nas_ip)->first();
    
    //     if (!$router) {
    //         return abort(404, 'Router not found');
    //     }
    
    //     // Get package IDs assigned to the router
    //     $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
    
    //     $packages = $packageIds->isNotEmpty()
    //         ? Package::with('bandwidth')
    //             ->whereIn('id', $packageIds)
    //             ->where('created_by', $router->created_by)
    //             ->where('type', 'Hotspot')
    //             ->get()
    //         : collect();
    
    //     // Capture MikroTik variables from query string
    //     $mac = $request->query('mac', '');  
    //     $chapId = $request->query('chapID', '');  
    //     $chapChallenge = $request->query('chapChallenge', '');  
    //     $loginLink = $request->query('loginLink', '');  
    
    //     return view('captive.login', compact('nas_ip', 'packages', 'mac', 'chapId', 'chapChallenge', 'loginLink'));
    // }

// public function showLogin($nas_ip = null, Request $request)
// {
//     if (!$nas_ip) {
//         return abort(400, 'NAS IP is required');
//     }

//     $router = Router::where('ip_address', $nas_ip)->first();
//     if (!$router) {
//         return abort(404, 'Router not found');
//     }

//     // Get package IDs assigned to the router
//     $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
//     $packages = $packageIds->isNotEmpty()
//         ? Package::with('bandwidth')
//             ->whereIn('id', $packageIds)
//             ->where('created_by', $router->created_by)
//             ->where('type', 'Hotspot')
//             ->get()
//         : collect();

//     // Capture MikroTik query parameters
//     $mac = $request->query('mac', '');
//     $chapId = $request->query('chapID', '');
//     $chapChallenge = $request->query('chapChallenge', '');
//     $loginLink = $request->query('loginLink', '');

//     // Detect invalid placeholders (MikroTik variables not replaced)
//     $invalidPlaceholders = ['$(mac)', '$(chap-id)', '$(chap-challenge)', '$(link-login-only)'];
//     if (in_array($mac, $invalidPlaceholders) || in_array($chapId, $invalidPlaceholders) || 
//         in_array($chapChallenge, $invalidPlaceholders) || in_array($loginLink, $invalidPlaceholders)) {
        
//         // Redirect to clean URL without placeholders
//         return redirect()->route('captive.showLogin', ['nas_ip' => $nas_ip]);
//     }

//     // Store valid parameters in session and redirect if they exist
//     if ($mac || $chapId || $chapChallenge || $loginLink) {
//         session([
//             'hotspot_mac'         => $mac,
//             'hotspot_chap_id'     => $chapId,
//             'hotspot_chap_challenge' => $chapChallenge,
//             'hotspot_login_link'  => $loginLink
//         ]);

//         return redirect()->route('captive.showLogin', ['nas_ip' => $nas_ip]);
//     }

//     // Retrieve session values if available
//     $mac = session('hotspot_mac', '');
//     $chapId = session('hotspot_chap_id', '');
//     $chapChallenge = session('hotspot_chap_challenge', '');
//     $loginLink = session('hotspot_login_link', '');

//     return view('captive.login', compact('nas_ip', 'packages', 'mac', 'chapId', 'chapChallenge', 'loginLink'));
// }

public function showLogin($nas_ip = null, Request $request)
{
    if (!$nas_ip) {
        return abort(400, 'NAS IP is required');
    }

    // Check if NAS exists
    $router = Router::where('ip_address', $nas_ip)->first();
    if (!$router) {
        return abort(404, 'Router not found');
    }

    // Get packages assigned to the NAS
    $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
    $packages = $packageIds->isNotEmpty()
        ? Package::with('bandwidth')
            ->whereIn('id', $packageIds)
            ->where('created_by', $router->created_by)
            ->where('type', 'Hotspot')
            ->get()
        : collect();

    // Capture query parameters
    $queryParams = $request->query();

    // MikroTik placeholders to check
    $invalidValues = ['$(mac)', '$(chap-id)', '$(chap-challenge)', '$(link-login-only)'];

    // Remove placeholders if they exist in the URL
    $hasInvalidValues = collect($queryParams)->contains(fn($value) => in_array($value, $invalidValues));

    if ($hasInvalidValues) {
        return redirect()->route('captive.showLogin', ['nas_ip' => $nas_ip]);
    }

    // If parameters exist, store them in session and redirect to a clean URL
    if (!empty($queryParams)) {
        session(['hotspot_login' => $queryParams]);
        return redirect()->route('captive.showLogin', ['nas_ip' => $nas_ip]);
    }

    // Retrieve stored session values
    $queryParams = session('hotspot_login', []);

    return view('captive.login', compact('nas_ip', 'packages', 'queryParams'));
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

        $ResultCode = $mpesastatus['ResultCode'] ?? null;
        $ResultDesc = $mpesastatus['ResultDesc'] ?? null;

        if ($ResultCode !== "0") {
            return response()->json(['success' => false, 'message' => 'Payment failed', 'ResultDesc' => $ResultDesc]);
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

        DB::connection('radius')->table('radcheck')->insert([
            'username'  => $customer->username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $customer->password,
            'created_by' => $router->created_by,
        ]);
        $group_name = 'package_' . $package->id;
        // Assign the Expired_Plan
        DB::connection('radius')->table('radusergroup')->insert([
            'username'  => $customer->username,
            'groupname' => $group_name,
            'priority'  => 1,
            'created_by' => $router->created_by,
        ]);
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
            'ResponseCode' => $ResultCode,
            'ResultDesc' => $ResultDesc
        ]);
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
