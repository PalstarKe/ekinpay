<?php

namespace App\Http\Controllers;
use App\Models\Package;
use App\Models\Bandwidth;
use App\Models\Utility;
use App\Models\User;
use App\Models\Nas;
use App\Models\Router;
use App\Models\RouterPackage;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{

    public function index()
    {
        if (Auth::user()->can('manage package')) {
            $packages = Package::with('bandwidth')->where('created_by', Auth::user()->creatorId())->get();
            $admin_payment_setting = Utility::getCompanyPaymentSetting(Auth::user()->creatorId());
            foreach ($packages as $package) {
                $package->status = $this->isPackageAssigned($package->id) ? 'Active' : 'Inactive';
            }
            // return view('package.index', compact('packages'));
            $pppoePackages = $packages->where('type', 'PPPoE');
            $hotspotPackages = $packages->where('type', 'Hotspot');
            return view('package.index', compact('pppoePackages', 'hotspotPackages', 'admin_payment_setting'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function isPackageAssigned($packageId)
    {
        return RouterPackage::where('package_id', $packageId)->exists();
    }
    public function create()
    {
        if(\Auth::user()->can('create package'))
        {
            $arrDevices = [
                'Radius' => __('Radius'),
                'API' => __('API'),
            ];
            $arrValidity = [
                'Minutes' => __('Minutes'),
                'Hours' => __('Hours'),
                'Days' => __('Days'),
                'Months' => __('Months'),
            ];
            $arrSpeed = [
                'K' => __('Kbps'),
                'M' => __('Mbps'),
            ];
            $arrfup = [
                'MB' => __('MB'),
                'GB' => __('GB'),
                'TB' => __('TB'),
            ];
            $arrType = [
                'PPPoE' => __('PPPoE'),
                'Hotspot' => __('Hotspot'),
            ];
            $arrTax = [
                'Inclusive' => __('Inclusive'),
                'Exclusive' => __('Exclusive'),
            ];
            return view('package.create', compact('arrDevices', 'arrValidity', 'arrSpeed', 'arrType', 'arrTax', 'arrfup'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('create package')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = [
            'name_plan'      => ['required',
                Rule::unique('packages', 'name_plan')->where(fn($query) => $query->where('created_by', Auth::user()->id))
            ],
            'price'          => 'required|numeric',
            'validity'       => 'required|integer',
            'validity_unit'  => 'required|string',
            'rate_down'      => 'required|integer',
            'rate_down_unit' => 'required|string',
            'rate_up'        => 'required|integer',
            'rate_up_unit'   => 'required|string',
            'tax_value'      => 'nullable|integer',
            'tax_type'       => 'nullable|string',
            // Additional package fields
            'device'         => 'required|string',
            'type'           => 'required|string',
            'shared_users'   => 'nullable|integer',
            'data_limit'     => 'nullable|numeric',
            'data_limit_unit'=> 'nullable|string',
            // Burst fields (required if enable_burst is "1")
            'enable_burst'      => 'nullable|in:1',
            'burst_limit'       => 'required_if:enable_burst,1|numeric',
            'burst_threshold'   => 'required_if:enable_burst,1|numeric',
            'burst_time'        => 'required_if:enable_burst,1|numeric',
            'burst_priority'    => 'required_if:enable_burst,1|integer',
            'burst_limit_at'    => 'required_if:enable_burst,1|numeric',
            // FUP fields (required if enable_fup is "1")
            'enable_fup'        => 'nullable|in:1',
            'fup_limit'         => 'required_if:enable_fup,1|numeric',
            'fup_unit'          => 'required_if:enable_fup,1|string',
            'fup_down_speed'    => 'required_if:enable_fup,1|numeric',
            'fup_down_unit'     => 'required_if:enable_fup,1|string',
            'fup_up_speed'      => 'required_if:enable_fup,1|numeric',
            'fup_up_unit'       => 'required_if:enable_fup,1|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('packages.index')->with('error', $validator->getMessageBag()->first());
        }

        // Retrieve validated data
        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            // Prepare package data without burst and FUP fields first
            $packageData = [
                'device'          => $validated['device'],
                'name_plan'       => $validated['name_plan'],
                'price'           => $validated['price'],
                'type'            => $validated['type'],
                'shared_users'    => $validated['shared_users'] ?? null,
                'validity'        => $validated['validity'],
                'validity_unit'   => $validated['validity_unit'],
                'tax_value'       => $validated['tax_value'] ?? null,
                'tax_type'        => $validated['tax_type'] ?? null,
                'data_limit'      => $validated['data_limit'] ?? null,
                'data_limit_unit' => $validated['data_limit_unit'] ?? null,
                'created_by'      => Auth::user()->id,
            ];

            // Include FUP fields if enabled
            if ($request->input('enable_fup') == 1) {
                $packageData['fup_limit']       = $validated['fup_limit'];
                $packageData['fup_unit']        = $validated['fup_unit'];
                $packageData['fup_down_speed']  = $validated['fup_down_speed'];
                $packageData['fup_down_unit']   = $validated['fup_down_unit'];
                $packageData['fup_up_speed']    = $validated['fup_up_speed'];
                $packageData['fup_up_unit']     = $validated['fup_up_unit'];
            }

            // Create the package record
            $package = Package::create($packageData);

            // Prepare burst value: combine burst fields into one space-separated string (if enabled)
            $burstValue = null;
            if ($request->input('enable_burst') == 1) {
                $burstValue = implode(' ', [
                    $validated['burst_limit'],
                    $validated['burst_threshold'],
                    $validated['burst_time'],
                    $validated['burst_priority'],
                    $validated['burst_limit_at']
                ]);
            }

            // Create Bandwidth record (burst field now stores the combined burst string)
            $bandwidth = Bandwidth::create([
                'package_id'     => $package->id,
                'name_plan'      => $package->name_plan,
                'rate_down'      => $request->rate_down,
                'rate_down_unit' => $request->rate_down_unit,
                'rate_up'        => $request->rate_up,
                'rate_up_unit'   => $request->rate_up_unit,
                'burst'          => $burstValue,
                'created_by'     => Auth::user()->id,
            ]);

            // RADIUS Settings
            $group_name = 'package_' . $package->id;
            $timelimit = match ($package->validity_unit) {
                'Minutes' => $package->validity * 60,
                'Hours'   => $package->validity * 3600,
                'Days'    => $package->validity * 86400,
                'Months'  => $package->validity * 2592000,
                default   => 0,
            };

            $down = $this->convertBandwidth($bandwidth->rate_down, $bandwidth->rate_down_unit);
            $up = $this->convertBandwidth($bandwidth->rate_up, $bandwidth->rate_up_unit);
            $datalimit = $this->convertDataLimit($package->data_limit, $package->data_limit_unit);
            $MikroRate = "{$bandwidth->rate_down}{$bandwidth->rate_down_unit}/{$bandwidth->rate_up}{$bandwidth->rate_up_unit}";

            // Prepare RADIUS check attributes for the package group
            $planCheckData = [
                ['groupname' => $group_name, 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept']
            ];
            if ($timelimit > 0) {
                $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Session-Timeout', 'op' => ':=', 'value' => $timelimit];
            }
            if ($datalimit > 0) {
                $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Max-Octets', 'op' => ':=', 'value' => $datalimit];
            }
            if ($package->shared_users) {
                $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Simultaneous-Use', 'op' => ':=', 'value' => $package->shared_users];
            }

            DB::table('radgroupcheck')->insert($planCheckData);

            // Insert default Expired Plan into radgroupcheck if not exists
            if (!DB::table('radgroupcheck')->where('groupname', 'Expired_Plan')->exists()) {
                DB::table('radgroupcheck')->insert([
                    ['groupname' => 'Expired_Plan', 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept']
                ]);
            }

            // Prepare RADIUS reply attributes for the package group
            $planReplyData = [
                ['groupname' => $group_name, 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => $MikroRate],
                ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => $down],
                ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => $up],
                ['groupname' => $group_name, 'attribute' => 'Acct-Interim-Interval', 'op' => ':=', 'value' => '60']
            ];

            DB::table('radgroupreply')->insert($planReplyData);

            // Insert default Expired Plan Reply into radgroupreply if not exists
            if (!DB::table('radgroupreply')->where('groupname', 'Expired_Plan')->exists()) {
                DB::table('radgroupreply')->insert([
                    ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '2K/2K'],
                    ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => '2000'],
                    ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => '2000'],
                    ['groupname' => 'Expired_Plan', 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => '300'],
                    ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Address-List', 'op' => ':=', 'value' => 'EXPIRED_POOL']
                ]);
            }

            DB::commit();

            return redirect()->route('packages.index')->with('success', __('Package & Bandwidth Created Successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('packages.index')->with('error', $e->getMessage());
        }
    }

    private function convertBandwidth($rate, $unit)
    {
        $multipliers = ['K' => 1000, 'M' => 1000000, 'G' => 1000000000];
        return isset($multipliers[$unit]) ? ($rate * $multipliers[$unit]) : $rate;
    }

    /**
     * Convert data limit to bytes.
     */
    private function convertDataLimit($data, $unit)
    {
        $multipliers = ['K' => 1024, 'M' => 1048576, 'G' => 1073741824];
        return isset($multipliers[$unit]) ? ($data * $multipliers[$unit]) : 0;
    }
    
    public function addPackage($name_plan, $price,  ){}
    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        $package = Package::findOrFail($id);

        if ($this->isPackagesActive($package)) {
            return redirect()->back()->with('error', __('Package is assigned and cannot be deleted.'));
        }

        $package->delete();
        return redirect()->route('packages.index')->with('success', __('Package deleted successfully.'));
    }

}
