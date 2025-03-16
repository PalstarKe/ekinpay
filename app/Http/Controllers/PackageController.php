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
                'Hours' => __('Hours'),
                'Days' => __('Days'),
                'Weeks' => __('Weeks'),
                'Months' => __('Months'),
            ];
            $arrSpeed = [
                'K' => __('Kbps'),
                'M' => __('Mbps'),
            ];
            $arrType = [
                'PPPoE' => __('PPPoE'),
                'Hotspot' => __('Hotspot'),
            ];
            return view('package.create', compact('arrDevices', 'arrValidity', 'arrSpeed', 'arrType'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function store(Request $request)
    // {
    //     if (\Auth::user()->can('create package')) {
    //         $rules = [
    //             'name_plan'      => ['required',
    //                 Rule::unique('packages', 'name_plan')->where(function ($query) {
    //                     return $query->where('created_by', \Auth::user()->id);
    //                 })
    //             ],
    //             'price'          => 'required|numeric',
    //             'validity'       => 'required|integer',
    //             'validity_unit'  => 'required',
    //             'rate_down'      => 'required|integer',
    //             'rate_down_unit' => 'required',
    //             'rate_up'        => 'required|integer',
    //             'rate_up_unit'   => 'required',
    //             'burst'          => 'nullable|integer',
    //         ];
    //         $validator = \Validator::make($request->all(), $rules);

    //         if($validator->fails())
    //         {
    //             $messages = $validator->getMessageBag();
    //             return redirect()->route('packages.index')->with('error', $messages->first());
    //         }

    //         $package = new Package();
    //         $package->name_plan = $request->name_plan;
    //         $package->price = $request->price;
    //         $package->type = $request->type;
    //         $package->validity = $request->validity;
    //         $package->validity_unit = $request->validity_unit;
    //         $package->shared_users = $request->shared_users;
    //         $package->device = $request->device;
    //         $package->created_by = Auth::user()->id;
    //         $package->save();
    //         // return $package->id();           
    //         $package = Package::find($package->id);

    //         if (!$package) {
    //             return response()->json(['error' => 'Package not found after saving'], 500);
    //         }

    //         $bandwidth = new Bandwidth();
    //         $bandwidth->package_id = $package->id;
    //         $bandwidth->name_plan = $package->name_plan;
    //         $bandwidth->rate_down = $request->rate_down;
    //         $bandwidth->rate_down_unit = $request->rate_down_unit;
    //         $bandwidth->rate_up = $request->rate_up;
    //         $bandwidth->rate_up_unit = $request->rate_up_unit;
    //         $bandwidth->burst = $request->burst;
    //         $bandwidth->created_by = Auth::user()->id;
    //         $bandwidth->save();

    //         // Assign package to RADIUS
    //         $planName = $package->name_plan;
            
    //         $timeout = '60';

    //         $timelimit = 0;

    //         if ($package->validity_unit == 'Minutes') {
    //             $timelimit = $package->validity * 60;
    //         } elseif ($package->validity_unit == 'Hours') {
    //             $timelimit = $package->validity * 3600;
    //         } elseif ($package->validity_unit == 'Days') {
    //             $timelimit = $package->validity * 86400;
    //         } elseif ($package->validity_unit == 'Months') {
    //             $timelimit = $package->validity * 2592000; // Assuming 30 days per month
    //         }
    //         $planTimeBank = $timelimit;

    //         $shared = $package->shared_users;

    //         function convertBandwidth($rate, $unit) {
    //             $multipliers = [
    //                 'K' => 1000,  // Kilobits to bits
    //                 'M' => 1000000, // Megabits to bits
    //                 'G' => 1000000000 // Gigabits to bits (if needed)
    //             ];
    //             return isset($multipliers[$unit]) ? ($rate * $multipliers[$unit]) : $rate;
    //         }
            
    //         $down = convertBandwidth($package->bandwidth->rate_down, $package->bandwidth->rate_down_unit);
    //         $up = convertBandwidth($package->bandwidth->rate_up, $package->bandwidth->rate_up_unit);

    //         $downm = $package->bandwidth->rate_down . $package->bandwidth->rate_down_unit;
    //         $upm = $package->bandwidth->rate_up . $package->bandwidth->rate_up_unit;
    //         $MikroRate = $downm . "/" . $upm;
            
    //         function convertDataLimit($data, $unit) {
    //             $multipliers = [
    //                 'K' => 1024, 
    //                 'M' => 1048576, 
    //                 'G' => 1073741824
    //             ];
    //             return isset($multipliers[$unit]) ? ($data * $multipliers[$unit]) : 0;
    //         }

    //         $datalimit = convertDataLimit($package->data_limit, $package->data_limit_unit);

    //         $bw_name = $package->bandwidth->name_plan;
    //         $bw_id = $package->bandwidth->id;

    //         $group_name = 'package_' . $package->id;
    //         $profileType = $package->typebp;
    //         $limitType = $package->limit_type;

    //         if ($profileType === 'Unlimited') {
    //             unset($datalimit); 
    //         } elseif ($profileType === 'Limited') {
    //             if ($limitType === 'Time_Limit') {
    //                 unset($datalimit);
    //             } elseif ($limitType === 'Data_Limit') {
    //                 unset($planTimeBank, $timelimit);
    //             }
    //         }
         

    //         DB::beginTransaction();

    //         try {
    //             // Insert into radgroupcheck
    //             $planCheckData = [
    //                 ['groupname' => $group_name, 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept']
    //             ];

    //             if (!empty($planTimeBank)) {
    //                 $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Session-Timeout', 'op' => ':=', 'value' => $planTimeBank];
    //             }
    //             if (!empty($datalimit)) {
    //                 $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Max-Octets', 'op' => ':=', 'value' => $datalimit];
    //             }
    //             if (!empty($shared)) {
    //                 $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Simultaneous-Use', 'op' => ':=', 'value' => $shared];
    //             }

    //             if (!empty($planCheckData)) {
    //                 DB::connection('radius')->table('radgroupcheck')->insert($planCheckData);
    //             }
    //             $planCheckExists = DB::connection('radius')->table('radgroupcheck')
    //                 ->where('groupname', 'Expired_Plan')
    //                 ->exists();

    //             if (!$planCheckExists) {
    //                 DB::connection('radius')->table('radgroupcheck')->insert([
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept'],
    //                     // ['groupname' => 'Expired_Plan', 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => '300'] // 5 min idle timeout
    //                 ]);
    //             }

    //             // Insert into radgroupreply
    //             $planReplyData = [];
                 
    //             if (!empty( $MikroRate)) {
    //                 $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' =>  $MikroRate];
    //             }

    //             if (!empty($down)) {
    //                 $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => $down];
    //             }
               
    //             if (!empty($up)) {
    //                 $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => $up];
    //             }

    //             // Acct-Interim-Interval is always added
    //             $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'Acct-Interim-Interval', 'op' => ':=', 'value' => '60'];

    //             if (!empty($planReplyData)) {
    //                 DB::connection('radius')->table('radgroupreply')->insert($planReplyData);
    //             }
    //             $planExists = DB::connection('radius')->table('radgroupreply')
    //                 ->where('groupname', 'Expired_Plan')
    //                 ->exists();

    //             if (!$planExists) {
    //                 DB::connection('radius')->table('radgroupreply')->insert([
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '256K/256K'],
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => '256000'],
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => '256000'],
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => '300'],
    //                     ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Address-List', 'op' => ':=', 'value' => 'EXPIRED_POOL']
    //                 ]);
    //             }


    //             DB::commit();
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }
    //         return redirect()->route('packages.index')->with('success', __('Package & Bandwidth Created Successfully.'));
    //     }else{
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }
    public function store(Request $request)
    {
        if (!\Auth::user()->can('create package')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        $rules = [
            'name_plan'      => ['required',
                Rule::unique('packages', 'name_plan')->where(fn($query) => $query->where('created_by', \Auth::user()->id))
            ],
            'price'          => 'required|numeric',
            'validity'       => 'required|integer',
            'validity_unit'  => 'required',
            'rate_down'      => 'required|integer',
            'rate_down_unit' => 'required',
            'rate_up'        => 'required|integer',
            'rate_up_unit'   => 'required',
            'burst'          => 'nullable|integer',
        ];
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('packages.index')->with('error', $validator->getMessageBag()->first());
        }
    
        DB::beginTransaction();
    
        try {
            // Create Package
            $package = Package::create([
                'name_plan'     => $request->name_plan,
                'price'         => $request->price,
                'type'          => $request->type,
                'validity'      => $request->validity,
                'validity_unit' => $request->validity_unit,
                'shared_users'  => $request->shared_users,
                'device'        => $request->device,
                'created_by'    => Auth::user()->id,
            ]);
    
            // Create Bandwidth
            $bandwidth = Bandwidth::create([
                'package_id'     => $package->id,
                'name_plan'      => $package->name_plan,
                'rate_down'      => $request->rate_down,
                'rate_down_unit' => $request->rate_down_unit,
                'rate_up'        => $request->rate_up,
                'rate_up_unit'   => $request->rate_up_unit,
                'burst'          => $request->burst,
                'created_by'     => Auth::user()->id,
            ]);
    
            // RADIUS Settings
            $group_name = 'package_' . $package->id;
            $timelimit = match ($package->validity_unit) {
                'Minutes' => $package->validity * 60,
                'Hours'   => $package->validity * 3600,
                'Days'    => $package->validity * 86400,
                'Months'  => $package->validity * 2592000, 
                default   => 0
            };
    
            $down = $this->convertBandwidth($bandwidth->rate_down, $bandwidth->rate_down_unit);
            $up = $this->convertBandwidth($bandwidth->rate_up, $bandwidth->rate_up_unit);
            $datalimit = $this->convertDataLimit($package->data_limit, $package->data_limit_unit);
            $MikroRate = "{$bandwidth->rate_down}{$bandwidth->rate_down_unit}/{$bandwidth->rate_up}{$bandwidth->rate_up_unit}";
    
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
    
            DB::connection('radius')->table('radgroupcheck')->insert($planCheckData);
    
            // Expired Plan Handling
            if (!DB::connection('radius')->table('radgroupcheck')->where('groupname', 'Expired_Plan')->exists()) {
                DB::connection('radius')->table('radgroupcheck')->insert([
                    ['groupname' => 'Expired_Plan', 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept']
                ]);
            }
    
            $planReplyData = [
                ['groupname' => $group_name, 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => $MikroRate],
                ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => $down],
                ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => $up],
                ['groupname' => $group_name, 'attribute' => 'Acct-Interim-Interval', 'op' => ':=', 'value' => '60']
            ];
    
            DB::connection('radius')->table('radgroupreply')->insert($planReplyData);
    
            // Expired Plan Reply Handling
            if (!DB::connection('radius')->table('radgroupreply')->where('groupname', 'Expired_Plan')->exists()) {
                DB::connection('radius')->table('radgroupreply')->insert([
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
    
    /**
     * Convert bandwidth to bits.
     */
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
