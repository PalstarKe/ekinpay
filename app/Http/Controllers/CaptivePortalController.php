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
    public function showLogin($nas_ip = null)
    {
        $router = Router::where('ip_address', $nas_ip)->first();
    
        if (!$router) {
            return abort(404, 'Router not found');
        }
    
        // Get package IDs assigned to the router
        $packageIds = RouterPackage::where('router_id', $router->id)->pluck('package_id');
    
        if ($packageIds->isNotEmpty()) {
            $packages = Package::with('bandwidth')
                ->whereIn('id', $packageIds)
                ->where('created_by', $router->created_by)
                ->where('type', 'Hotspot')
                ->get();
        } else {
            $packages = collect();
        }
    
        return view('captive.login', compact('nas_ip', 'packages'));
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
    
        $existingCustomer = Customer::where('fullname', $phone)
        ->where('mac_address', $request->mac_address)
        ->first();

        // Create new customer
        if (!$existingCustomer) {
            $customer = new Customer();
            $customer->fullname     = $phone;
            $customer->username     = $request->mac_address;
            $customer->account      = $request->mac_address;
            $customer->password     = $request->mac_address;
            $customer->contact      = $phone;
            $customer->created_by   = $router->created_by;
            $customer->service      = 'Hotspot';
            $customer->auto_renewal = 1;
            $customer->is_active    = 0;
            $customer->mac_address  = $request->mac_address;
            $customer->package      = $package->name_plan;
            $customer->save();
        }

        $mpesaResponse = CustomHelper::initiateHotspotSTKPush($phone, $package->price, $router->created_by);

        $mpesaResponse = (array) $mpesaResponse; 

        $checkoutRequestID = $mpesaResponse['CheckoutRequestID'] ?? null;

        Log::info("Response for Checkout:", ['CheckoutRequestID' => $checkoutRequestID]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent',
            'checkoutRequestID' => $checkoutRequestID
        ]);

    }

    public function processQueryMpesa(Request $request){

        $rules = [
            'ref'      => 'required'
        ];

        $mpesastatus = CustomHelper::initiateHotspotSTKPush($phone, $package->price, $router->created_by);

        $mpesastatus = (array) $mpesaResponse; 

        $responseCode = $mpesastatus['CheckoutRequestID'] ?? null;

        Log::info("Response for Checkout:", ['CheckoutRequestID' => $checkoutRequestID]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent',
            'checkoutRequestID' => $checkoutRequestID
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
