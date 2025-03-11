<?php

namespace App\Http\Controllers;
use App\Models\Package;
use App\Models\Bandwidth;
use App\Models\Utility;
use App\Models\User;
use App\Models\Nas;
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
                $package->status = $this->isPackagesActive($package) ? 'Active' : 'Inactive';
            }
            // return view('package.index', compact('packages'));
            $pppoePackages = $packages->where('type', 'PPPoE');
            $hotspotPackages = $packages->where('type', 'Hotspot');
            return view('package.index', compact('pppoePackages', 'hotspotPackages', 'admin_payment_setting'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function isPackagesActive($package)
    {
        return !empty($package->assigned_to);
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

    public function store(Request $request)
    {
        if (\Auth::user()->can('create package')) {
            $rules = [
                'name_plan'      => ['required',
                    Rule::unique('packages', 'name_plan')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    })
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

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('packages.index')->with('error', $messages->first());
            }

            $package = new Package();
            $package->name_plan = $request->name_plan;
            $package->price = $request->price;
            $package->type = $request->type;
            $package->validity = $request->validity;
            $package->validity_unit = $request->validity_unit;
            $package->shared_users = $request->shared_users;
            $package->device = $request->device;
            $package->created_by = Auth::user()->id;
            $package->save();
            // return $package->id();           
            $package = Package::find($package->id);

            if (!$package) {
                return response()->json(['error' => 'Package not found after saving'], 500);
            }

            $bandwidth = new Bandwidth();
            $bandwidth->package_id = $package->id;
            $bandwidth->name_plan = $package->name_plan;
            $bandwidth->rate_down = $request->rate_down;
            $bandwidth->rate_down_unit = $request->rate_down_unit;
            $bandwidth->rate_up = $request->rate_up;
            $bandwidth->rate_up_unit = $request->rate_up_unit;
            $bandwidth->burst = $request->burst;
            $bandwidth->created_by = Auth::user()->id;
            $bandwidth->save();

            return redirect()->route('packages.index')->with('success', __('Package & Bandwidth Created Successfully.'));
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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
