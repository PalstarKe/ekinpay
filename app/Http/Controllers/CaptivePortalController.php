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
use Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;


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

}
