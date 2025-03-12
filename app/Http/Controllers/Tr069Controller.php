<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Tr069Controller extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage tr069')) {
            // $packages = Package::with('bandwidth')->where('created_by', Auth::user()->creatorId())->get();
            // $admin_payment_setting = Utility::getCompanyPaymentSetting(Auth::user()->creatorId());
            // foreach ($packages as $package) {
            //     $package->status = $this->isPackagesActive($package) ? 'Active' : 'Inactive';
            // }
            // // return view('package.index', compact('packages'));
            // $pppoePackages = $packages->where('type', 'PPPoE');
            // $hotspotPackages = $packages->where('type', 'Hotspot');
            return view('tr069.index', compact());
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

}
