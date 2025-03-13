<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Package;
use App\Models\Utility;
use Auth;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CustomerController extends Controller
{

    public function dashboard()
    {
        $data['invoiceChartData'] = \Auth::user()->invoiceChartData();

        return view('customer.dashboard', $data);
    }
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage customer')) {
            $query = Customer::where('created_by', \Auth::user()->creatorId());

            // Apply search filter if a search query is provided
            if (!empty($request->search)) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
                });
            }
            // Get the filtered results
            $customers = $query->get();
            foreach ($customers as $customer) {
                $customer->online = DB::connection('radius')
                    ->table('radacct')
                    ->whereNull('acctstoptime')
                    ->where('username', $customer->username)
                    ->exists(); // Returns true (Online) or false (Offline)
            }

            $pppoeCustomers =  $customers->where('service', 'PPPoE');
            $hotspotCustomers =  $customers->where('service', 'Hotspot');
            $actcustomers = $customers->where('is_active', 1);
            $suscustomers = $customers->where('is_active', 0);
            $expcustomers = $customers->where('expiry_status', 'off');

            return view('customer.index', compact('customers','pppoeCustomers', 'hotspotCustomers', 'actcustomers', 'suscustomers', 'expcustomers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create customer'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

            $arrType = [
                'PPPoE' => __('PPPoE'),
            ];

            $arrPackage = Package::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'PPPoE')
            ->pluck('name_plan')
            ->toArray();

            $latest = Customer::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();

            if (!$latest || empty($latest->account)) {
                $customerN = Auth::user()->customerNumberFormat(1); // Start from 1 if no existing account
            } else {
                // Extract the numeric part of the account and increment it
                preg_match('/\d+$/', $latest->account, $matches);
                $nextNumber = isset($matches[0]) ? (int)$matches[0] + 1 : 1;

                $customerN = Auth::user()->customerNumberFormat($nextNumber);
            }

            return view('customer.create', compact('customFields', 'customerN', 'arrType', 'arrPackage'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create customer')) 
        {
            // Convert 07xxxxxxxx or 01xxxxxxxx to 2547xxxxxxxx / 2541xxxxxxxx
            if (preg_match('/^(07|01)(\d{8})$/', $request->contact, $matches)) {
                $request->merge([
                    'contact' => '254' . substr($matches[0], 1), // Removes the leading 0 and adds 254
                ]);
            }

            // Ensure username is the same as account if it's null
            $request->merge([
                'username' => $request->username ?? $request->account,
            ]);
            $rules = [
                'fullname'  => 'required|string|max:255',
                'username'  => 'nullable|string|max:255',
                'account'   => 'nullable|string|max:255',
                'email'     => [
                    'required',
                    'email',
                    Rule::unique('customers')->where(function ($query) {
                        return $query->whereRaw('LOWER(email) = LOWER(?)', [request('email')])
                                     ->where('created_by', \Auth::user()->id);
                    })
                ],
                'contact'   => ['required', 'regex:/^254[17][0-9]{8}$/'],
                'service'   => 'nullable|string|max:255',
                'mac_address' => 'nullable|string|max:255|unique:customers,mac_address',
                'static_ip'   => 'nullable|ip',
                'expiry'      => 'nullable|date',
            ];
   
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->route('customer.index')->with('error', $validator->errors()->first());
            }

            // Check Customer Limit
            $user = \Auth::user();
            $creator = User::find($user->creatorId());
            $totalCustomers = $user->countCustomers();
            $plan = Plan::find($creator->plan);
            $defaultLanguage = DB::table('settings')->where('name', 'default_language')->value('value');

            if ($totalCustomers < $plan->max_customers || $plan->max_customers == -1) 
            {
                $customer = new Customer();
                $customer->customer_id  = $this->customerNumber();
                $customer->fullname     = $request->fullname;
                $customer->username     = $request->username;
                $customer->account      = $request->account;
                $customer->password     = $request->password;
                $customer->email        = $request->email;
                $customer->contact      = $request->contact;
                // $customer->tax_number   = $request->tax_number;
                $customer->created_by   = \Auth::user()->creatorId();
                $customer->service      = $request->service;
                $customer->auto_renewal = 1;
                $customer->is_active    = 0;
                $customer->mac_address  = $request->mac_address;
                $customer->maclock      = 1;
                $customer->static_ip    = $request->static_ip;
                $customer->sms_group    = $request->sms_group;
                $customer->charges      = $request->charges;
                $customer->package      = $request->package;
                $customer->apartment    = $request->apartment;
                $customer->location     = $request->location;
                $customer->housenumber  = $request->housenumber;
                $customer->expiry       = $request->expiry;
                $customer->expiry_status= $request->expiry_status;
                $customer->lang         = !empty($defaultLanguage) ? $defaultLanguage : 'en';
                $customer->balance      = 0.00;
                $customer->save();
                
                // $id = Customer::find($customer->id);
                DB::connection('radius')->table('radcheck')->insert([
                    'username'  => $customer->username,
                    'attribute' => 'Cleartext-Password',
                    'op'        => ':=',
                    'value'     => $request->password,
                ]);

                // Assign the Expired_Plan
                DB::connection('radius')->table('radusergroup')->insert([
                    'username'  => $customer->username,
                    'groupname' => 'Expired_Plan',
                    'priority'  => 1,
                ]);

                // Custom Field Handling
                if ($request->has('customField')) {
                    CustomField::saveData($customer, $request->customField);
                }

                // Notification Handling
                $settings = Utility::settings(\Auth::user()->creatorId());
                if (!empty($settings['twilio_customer_notification']) && $settings['twilio_customer_notification'] == 1) {
                    Utility::send_twilio_msg($request->contact, 'new_customer', [
                        'user_name'      => \Auth::user()->name,
                        'customer_name'  => $customer->fullname,
                        'customer_email' => $customer->email,
                    ]);
                }

                return redirect()->route('customer.show', ['customer' => encrypt($customer->id)])->with('success', __('Customer successfully created.'));
            } 
            else 
            {
                return redirect()->back()->with('error', __('Your Customer limit is over. Please upgrade your plan.'));
            }
        } 
        else 
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        try {
            $id       = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Customer Not Found.'));
        }
        // $id       = \Crypt::decrypt($ids);
        $customer = Customer::find($id);

        $arrType = [
            'PPPoE' => __('PPPoE'),
        ];

        $arrPackage = Package::where('created_by', \Auth::user()->creatorId())
        ->where('type', 'PPPoE')
        ->pluck('name_plan')
        ->toArray();

        $expiryDate = $customer->expiry_extended ?? $customer->expiry;
        $currentDate = Carbon::now();
        $expiryStatus = 'No Expiry Set';

        if ($expiryDate) {
            $expiryDate = Carbon::parse($expiryDate);
            $diff = $currentDate->diff($expiryDate);

            if ($expiryDate->isFuture()) {
                $expiryStatus = "{$diff->d} Days {$diff->h} Hrs";
            } elseif ($expiryDate->isPast()) {
                $expiryStatus = "Expired {$diff->d} Days";
            } else {
                $expiryStatus = "Expires today";
            }
        }
        // If expiry_extended is used, mark it as extended
        if ($customer->expiry_extended) {
            $diffExtended = $currentDate->diff($expiryDate);
            $expiryStatus = "Extended for {$diffExtended->d} Dys {$diffExtended->h} Hrs";
        }

        CustomHelper::lockMac($customer);
        
        $online = DB::connection('radius')->table('radacct')->whereNull('acctstoptime')->where('username', $customer->username)->exists();
        $session = DB::connection('radius')->table('radacct')->whereNull('acctstoptime')->where('username', $customer->username)->select('framedipaddress as ip', 'acctsessiontime as uptime')
            ->first();
        $uptime = DB::connection('radius')
            ->table('radacct')
            ->whereNull('acctstoptime')
            ->where('username', $customer->username)
            ->select('acctsessiontime as uptime')
            ->first();
        
        if ($uptime) {
            $displayUptime = gmdate('H:i:s', $uptime->uptime);
        } else {
            $lastSession = DB::connection('radius')
                ->table('radacct')
                ->whereNotNull('acctstoptime')
                ->where('username', $customer->username)
                ->orderByDesc('acctstoptime')
                ->select('acctstoptime')
                ->first();

            if ($lastSession) {
                $offlineSeconds = Carbon::parse($lastSession->acctstoptime)->diffInSeconds(Carbon::now());
                $displayUptime = "Offline for " . gmdate('H:i:s', $offlineSeconds);
            } else {
                $displayUptime = "No session history";
            }
        }

        $dataUsage = DB::connection('radius')->table('radacct')
            ->where('username', $customer->username)
            ->selectRaw('COALESCE(SUM(acctoutputoctets), 0) as download, COALESCE(SUM(acctinputoctets), 0) as upload')
            ->first();

        $activeUsage = DB::connection('radius')->table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->selectRaw('COALESCE(SUM(acctoutputoctets), 0) as download, COALESCE(SUM(acctinputoctets), 0) as upload')
            ->first();

        $downloadMB = round($activeUsage->download / 1048576, 2);
        $uploadMB = round($activeUsage->upload / 1048576, 2);


        $transactions = Transaction::where('user_id', $id)->where('user_type', 'Customer')->get();
        $invoices = Invoice::where('customer_id', $id)->get();

        $authLogs = DB::connection('radius')->table('radacct')
        ->where('username', $customer->username)
        ->orderBy('acctstarttime', 'desc')
        ->get();

        $deviceVendor = $customer->mac_address ? CustomHelper::getMacVendor($customer->mac_address) : 'N/A';
    
        return view('customer.show', compact('customer', 'expiryStatus', 'online', 'session','arrType', 'displayUptime', 'arrPackage', 'dataUsage', 'downloadMB', 'uploadMB', 'transactions', 'invoices', 'authLogs', 'deviceVendor'));
    }

    public function edit($id)
    {
        if(\Auth::user()->can('edit customer'))
        {
            $customer              = Customer::find($id);
            $customer->customField = CustomField::getData($customer, 'customer');

            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

            return view('customer.edit', compact('customer', 'customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Customer $customer)
    {

        if(\Auth::user()->can('edit customer'))
        {
            // Convert 07xxxxxxxx or 01xxxxxxxx to 2547xxxxxxxx / 2541xxxxxxxx
            if (preg_match('/^(07|01)(\d{8})$/', $request->contact, $matches)) {
                $request->merge([
                    'contact' => '254' . substr($matches[0], 1), // Removes the leading 0 and adds 254
                ]);
            }

            // Ensure username is the same as account if it's null
            $request->merge([
                'username' => $request->username ?? $request->account,
            ]);
            $rules = [
                'fullname'  => 'required|string|max:255',
                'username'  => 'nullable|string|max:255',
                'account'   => 'nullable|string|max:255',
                'email'     => [
                    'required',
                    'email',
                    Rule::unique('customers')->where(function ($query) {
                        return $query->whereRaw('LOWER(email) = LOWER(?)', [request('email')])
                                     ->where('created_by', \Auth::user()->id);
                    })
                ],
                'contact'   => ['required', 'regex:/^254[17][0-9]{8}$/'],
                'service'   => 'nullable|string|max:255',
                'mac_address' => 'nullable|string|max:255|unique:customers,mac_address',
                'static_ip'   => 'nullable|ip',
                'expiry'      => 'nullable|date',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('error', $messages->first());
            }

            $customer->fullname     = $request->fullname;
            $customer->username     = $request->username;
            $customer->account      = $request->account;
            $customer->password     = $request->password;
            $customer->email        = $request->email;
            $customer->contact      = $request->contact;
            // $customer->tax_number   = $request->tax_number;
            $customer->created_by   = \Auth::user()->creatorId();
            $customer->service      = $request->service;
            $customer->mac_address  = $request->mac_address;
            $customer->static_ip    = $request->static_ip;
            $customer->sms_group    = $request->sms_group;
            $customer->charges      = $request->charges;
            $customer->package      = $request->package;
            $customer->apartment    = $request->apartment;
            $customer->location     = $request->location;
            $customer->housenumber  = $request->housenumber;
            $customer->save();

            CustomField::saveData($customer, $request->customField);

            return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('success', __('Customer successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function updateExpiry(Request $request, $id)
    {
        $request->validate([
            'expiry' => 'required|date',
        ]);
    
        $customer = Customer::findOrFail($id);
        $customer->expiry = Carbon::parse($request->expiry);
        $customer->save();
        
        return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('success', __('Expiry date updated successfully.'));
    }

    public function updateExtend(Request $request, $id)
    {
        $request->validate([
            'expiry_extended' => 'required|date',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->expiry_extended = Carbon::parse($request->expiry_extended);
        $customer->expiry_status = 'on';
        $customer->save();

        // If customer was in expired plan, move them back to their package
        // $this->assignCustomerPackage($customer->id);
        CustomHelper::assignCustomerPackage($customer->id);

        return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('success', __('Expiry extended successfully.'));
    }

    public function depositCash(Request $request, $id)
    {
        $request->validate([
            'balance' => 'required|numeric', // Ensure balance is a number
        ]);

        $customer = Customer::findOrFail($id);
        // $package = Package::where('name_plan', $customer->package)->firstOrFail();
        // $customer->expiry_status = 'on';
        $customer->balance += $request->balance;

        // If balance was 0 and status was OFF, reactivate customer
        if ($customer->expiry_status == 'off') {
            $customer->expiry_status = 'on';
            $customer->save();

            // Assign package to customer in RADIUS
            // $this->assignCustomerPackage($customer->id);
            CustomHelper::assignCustomerPackage($customer->id);
        } else {
            $customer->save();
        }

        return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('success', __('Balance updated successfully.'));
    }
    
    public function refreshAccount(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $result = CustomHelper::refreshCustomerInRadius($customer);

        if ($result['status'] === 'success') {
            return redirect()->route('customer.show', ['customer' => encrypt($id)])
                ->with('success', __($result['message']));
        } else {
            return redirect()->route('customer.show', ['customer' => encrypt($id)])
                ->with('error', __($result['message']));
        }
    }

    public function changePlan(Request $request, $id)
    {
        $request->validate([
            'package' => 'required|exists:packages,name_plan',
        ]);
    
        $customer = Customer::findOrFail($id);
        $newPackage = Package::where('name_plan', $request->package)->firstOrFail();
    
        if ($customer->package === $newPackage->name_plan) {
            return redirect()->back()->with('error', __('Customer is already on this package.'));
        }
    
        $customer->package = $newPackage->name_plan;
        $customer->save();
        // $this->updatePlan($customer);
        CustomHelper::updatePlan($customer);
    
        return redirect()->route('customer.show', ['customer' => encrypt($id)])->with('success', __('Package updated successfully.'));
    }
    
    public function deactivate(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Toggle activation status
        $customer->is_active = !$customer->is_active;
        $customer->save();

        if (!$customer->is_active) {
            // User is deactivated -> Disconnect & Expire
            // $this->handleDeactivation($customer);
            CustomHelper::handleDeactivation($customer);
            $message = __('Customer deactivated and moved to expired plan.');
        } else {
            // User is activated -> Restore their plan
            // $this->updatePlan($customer);
            CustomHelper::updatePlan($customer);
            $message = __('Customer activated successfully.');
        }

        return redirect()->route('customer.show', ['customer' => encrypt($id)])
            ->with('success', $message);
    }

    public function clearMac(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Clear MAC address
        $customer->mac_address = null;
        $customer->save();

        return redirect()->route('customer.show', ['customer' => encrypt($id)])
            ->with('success', __('MAC address cleared successfully.'));
    }

    public function destroy(Customer $customer)
    {
        if(\Auth::user()->can('delete customer'))
        {
            if($customer->created_by == \Auth::user()->creatorId())
            {
                $customer->delete();

                return redirect()->route('customer.index')->with('success', __('Customer successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function customerNumber()
    {
        $latest = Customer::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }
        return $latest->customer_id + 1;
    }

    public function customerLogout(Request $request)
    {
        \Auth::guard('customer')->logout();

        $request->session()->invalidate();

        return redirect()->route('customer.login');
    }

    public function payment(Request $request)
    {

        if(\Auth::user()->can('manage customer payment'))
        {
            $category = [
                'Invoice' => 'Invoice',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Customer')->where('type', 'Payment');
            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $payments = $query->get();

            return view('customer.payment', compact('payments', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {
        if(\Auth::user()->can('manage customer payment'))
        {
            $category = [
                'Invoice' => 'Invoice',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Customer');

            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $transactions = $query->get();

            return view('customer.transaction', compact('transactions', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profile()
    {
        $userDetail              = \Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'customer');
        $customFields            = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

        return view('customer.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = Customer::findOrFail($userDetail['id']);

        $this->validate(
            $request, [
                        'name' => 'required|max:120',
                        'contact' => 'required',
                        'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                    ]
        );

        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }

            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if(!empty($request->profile))
        {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']    = $request['name'];
        $user['email']   = $request['email'];
        $user['contact'] = $request['contact'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }


    public function export()
    {
        $name = 'customer_' . date('Y-m-d i:h:s');
        $data = Excel::download(new CustomerExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }

    public function importFile()
    {
        return view('customer.import');
    }
    
    public function customerImportdata(Request $request)
    {
        session_start();
    
        if (!$request->hasFile('csv_file')) {
            return response()->json([
                'html' => true,
                'response' => '<h3 class="text-danger text-center">No file uploaded</h3></br>',
            ]);
        }
    
        $file = $request->file('csv_file');
    
        $file_data = [];
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $file_data[] = $row;
            }
            fclose($handle);
        }
    
        if (empty($file_data) || count($file_data) < 2) {
            return response()->json([
                'html' => true,
                'response' => '<h3 class="text-danger text-center">File is empty or only contains headers</h3></br>',
            ]);
        }
    
        $headers = array_map('strtolower', $file_data[0]); 
        unset($file_data[0]); 
    
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered"><tr>';
    
        foreach ($file_data as $row) {
            $rowData = array_combine($headers, $row);
            if (!$rowData) continue;
    
            $customerByEmail = Customer::where('email', $rowData['email'] ?? null)->first();
    
            if (empty($customerByEmail)) {
                try {
                    if (empty($rowData['account'])) {
                        $latest = Customer::where('created_by', \Auth::user()->creatorId())->latest()->first();
    
                        if (!$latest || empty($latest->account)) {
                            $customerN = Auth::user()->customerNumberFormat(1);
                        } else {
                            preg_match('/\d+$/', $latest->account, $matches);
                            $nextNumber = isset($matches[0]) ? (int)$matches[0] + 1 : 1;
                            $customerN = Auth::user()->customerNumberFormat($nextNumber);
                        }
                        $rowData['account'] = $customerN;
                    }
    
                    $customerData = new Customer();
                    $customerData->customer_id      = $this->customerNumber();
                    $customerData->fullname         = $rowData['fullname'] ?? null;
                    $customerData->username         = $rowData['username'] ?? null;
                    $customerData->account          = $rowData['account'];
                    $customerData->email            = $rowData['email'] ?? null;
                    $customerData->expiry_extended  = $rowData['expiry_extended'] ?? null;
                    $customerData->contact          = $rowData['contact'] ?? null;
                    $customerData->avatar           = $rowData['avatar'] ?? '';
                    $customerData->created_by       = \Auth::user()->creatorId();
                    $customerData->is_active        = 1;
                    $customerData->service          = $rowData['service'] ?? null;
                    $customerData->auto_renewal     = $rowData['auto_renewal'] ?? 1;
                    $customerData->mac_address      = $rowData['mac_address'] ?? null;
                    $customerData->static_ip        = $rowData['static_ip'] ?? null;
                    $customerData->sms_group        = $rowData['sms_group'] ?? null;
                    $customerData->charges          = $rowData['charges'] ?? null;
                    $customerData->package          = $rowData['package'] ?? null;
                    $customerData->apartment        = $rowData['apartment'] ?? null;
                    $customerData->location         = $rowData['location'] ?? null;
                    $customerData->housenumber      = $rowData['housenumber'] ?? null;
                    $customerData->expiry           = $rowData['expiry'] ?? null;
                    $customerData->expiry_status    = $rowData['expiry_status'] ?? null;
                    $customerData->lang             = $rowData['lang'] ?? 'en';
                    $customerData->balance          = $rowData['balance'] ?? '0.00';
                    $customerData->save();
    
                    $radiusUsername = $rowData['username'] ?? $rowData['account'];
                    $radiusPassword = $rowData['password'] ?? 'defaultpass';
                    $expiryDate = $rowData['expiry'] ?? null;
                    $importPackage = $rowData['package'] ?? null;
                    $uploadDate = now();
                    $createdBy = \Auth::user()->creatorId();
    
                    $package = Package::where('name_plan', $importPackage)->first();
                    $group_name = $package ? 'package_' . $package->id : 'Expired_Plan';
    
                    if ($expiryDate && strtotime($expiryDate) < strtotime($uploadDate)) {
                        $group_name = 'Expired_Plan';
                    }
    
                    \DB::connection('radius')->table('radcheck')->insert([
                        'username' => $radiusUsername,
                        'attribute' => 'Cleartext-Password',
                        'op' => ':=',
                        'value' => $radiusPassword,
                        'created_by' => $createdBy,
                    ]);
    
                    \DB::connection('radius')->table('radusergroup')->insert([
                        'username' => $radiusUsername,
                        'groupname' => $group_name,
                        'priority' => 1,
                        'created_by' => $createdBy,
                    ]);
    
                } catch (\Exception $e) {
                    \Log::error("Customer import error: " . $e->getMessage());
                    $flag = 1;
                    $html .= '<tr>';
                    foreach ($rowData as $column) {
                        $html .= '<td>' . ($column ?? '-') . '</td>';
                    }
                    $html .= '</tr>';
                }
            } else {
                $flag = 1;
                $html .= '<tr>';
                foreach ($rowData as $column) {
                    $html .= '<td>' . ($column ?? '-') . '</td>';
                }
                $html .= '</tr>';
            }
        }
    
        $html .= '</table><br />';
    
        return response()->json([
            'html' => $flag === 1,
            'response' => $flag === 1 ? $html : 'Data Imported Successfully',
        ]);
    }
    
    public function searchCustomers(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->can('manage customer')) {
            $customers = [];
            $search    = $request->search;
            if ($request->ajax() && isset($search) && !empty($search)) {
                $customers = Customer::select('id as value', 'name as label', 'email')->where('is_active', '=', 1)->where('created_by', '=', Auth::user()->getCreatedBy())->Where('name', 'LIKE', '%' . $search . '%')->orWhere('email', 'LIKE', '%' . $search . '%')->get();

                return json_encode($customers);
            }

            return $customers;
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getLiveUsage($username)
    {
        // Fetch the last two records for the user
        $records = DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime') // Ensure active session
            ->orderByDesc('acctupdatetime') // Sort by last update time
            ->limit(2)
            ->get();
    
        if ($records->count() < 2) {
            return response()->json([
                'download' => 0,
                'upload' => 0,
                'timestamp' => now()->format('H:i:s')
            ]);
        }
    
        // Get the two most recent records
        $latest = $records[0];
        $previous = $records[1];
    
        // Ensure timestamps exist
        if (!$latest->acctupdatetime || !$previous->acctupdatetime) {
            return response()->json([
                'download' => 0,
                'upload' => 0,
                'timestamp' => now()->format('H:i:s')
            ]);
        }
    
        // Calculate time difference in seconds
        $timeDiff = strtotime($latest->acctupdatetime) - strtotime($previous->acctupdatetime);
        if ($timeDiff <= 0) {
            return response()->json([
                'download' => 0,
                'upload' => 0,
                'timestamp' => now()->format('H:i:s')
            ]);
        }
    
        // Ensure octets are greater than previous (handle resets)
        $downloadOctets = max(0, $latest->acctoutputoctets - $previous->acctoutputoctets);
        $uploadOctets = max(0, $latest->acctinputoctets - $previous->acctinputoctets);
    
        // Convert to Mbps: (Bytes * 8) / (Seconds * 1024 * 1024)
        $downloadSpeed = ($downloadOctets * 8) / ($timeDiff * 1024 * 1024);
        $uploadSpeed = ($uploadOctets * 8) / ($timeDiff * 1024 * 1024);
    
        return response()->json([
            'download' => round($downloadSpeed, 2), // Mbps
            'upload' => round($uploadSpeed, 2), // Mbps
            'timestamp' => now()->format('H:i:s')
        ]);
    }
    

    public function handle()
    {
        $expiredUsers = RadiusUser::where('expiration_date', '<', now())->get();
        
        foreach ($expiredUsers as $user) {
            $user->status = 'expired';
            $user->save();
        }
        
        $this->info('Expired users updated successfully.');
    }
   
    public function useBalance(Request $request, $customerId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:installation,package',
        ]);

        $customer = Customer::findOrFail($customerId);

        if ($customer->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }

        // Create a new invoice
        $invoice = CustomHelper::generateInvoice($customer, $request->type, $request->amount);

        // Deduct customer balance
        // $customer->balance -= $request->amount;
        // $customer->save();

        // Record the payment
        $invoicePayment = CustomHelper::recordInvoicePayment($customer, $invoice, $request->amount);

        // Update invoice status
        CustomHelper::updateInvoiceStatus($invoice);

        $invoicePayment->refresh();
        // Process transaction
        CustomHelper::processTransaction($invoicePayment, $invoice->customer_id);

        // Update user balance
        Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'credit');

        // Record bank transaction
        Utility::bankAccountBalance(auth()->id(), $request->amount, 'credit');

        // Send notification
        CustomHelper::sendInvoiceNotification($customer, $invoice, $request->amount);

        return back()->with('success', 'Transaction completed successfully!');
    }
}
