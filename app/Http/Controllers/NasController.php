<?php

namespace App\Http\Controllers;
use App\Models\Utility;
use App\Models\User;
use App\Models\Nas;
use App\Models\Router;
use App\Models\Package;
use App\Models\RouterPackage;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\RSA;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class NasController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage nas'))
        {
            // $nases = Nas::where('created_by', \Auth::user()->creatorId())->get();
            $nases = Nas::with('routers.packages.bandwidth')->where('created_by', \Auth::user()->creatorId())->get();
            foreach ($nases as $nas) {
                
                $nas->status = $this->isNasOnline($nas->nasname) ? 'Online' : 'Offline';
            }

            return view('nas.index', compact('nases'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function isNasOnline($nasIp)
    {
        $port = 8728; // Change to your router's service port (e.g., 8291 for MikroTik API)
        $timeout = 5;

        if (is_callable('fsockopen') && false === stripos(ini_get('disable_functions'), 'fsockopen')) {
            $fsock = @fsockopen($nasIp, $port, $errno, $errstr, $timeout);
            if ($fsock) {
                fclose($fsock);
                return true;
            }
        } elseif (is_callable('stream_socket_client') && false === stripos(ini_get('disable_functions'), 'stream_socket_client')) {
            $connection = @stream_socket_client("$nasIp:$port", $errno, $errstr, $timeout);
            if ($connection) {
                fclose($connection);
                return true;
            }
        }
        return false;
    }


    private function checkNasStatus($nasIp, $nasSecret)
    {
        $command = "echo 'User-Name=future Password=FutureFirm2025' | radclient -x $nasIp:3799 status $nasSecret";
        $output = shell_exec($command);
    
        if (strpos($output, 'Received response') !== false) {
            return 1;
        } else {
            return 2;
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create nas'))
        {
            return view('nas.create');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create nas')) {
            $rules = [
                'site_name' => [
                    'required',
                    'string',
                    Rule::unique('radius.nas', 'shortname')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('nas.index')->with('error', $messages->first());
            }

            // Step 1: Generate OpenVPN Client and Get Static IP
            $vpnScriptPath = "/var/www/html/openvpn/openvpn-client.sh";
            $clientName = escapeshellarg($request->site_name);
            $vpnCommand = "sudo -u www-data /bin/bash $vpnScriptPath $clientName";
            $vpnOutput = shell_exec($vpnCommand);

            \Log::info("OpenVPN output for {$request->site_name}: " . $vpnOutput);

            preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $vpnOutput, $matches);
            $staticIp = $matches[0] ?? null;

            if (!$staticIp) {
                return redirect()->route('nas.index')->with('error', "❌ Failed to generate OpenVPN client.");
            }

            // Step 2: Generate a Secret for FreeRADIUS
            $nasSecret = config('radius.default_secret') ?? Str::random(16);

            // Step 3: Store NAS in FreeRADIUS Database
            $nas = new Nas();
            $nas->nasname = $staticIp;
            $nas->shortname = $request->site_name;
            $nas->secret = $nasSecret;
            $nas->nasapi = 0; // Assuming no API access
            $nas->type = "other";
            $nas->server = 'radius';
            $nas->community = '';
            $nas->created_by = \Auth::user()->creatorId();
            $nas->save();
            
            //Link Nas to Routers Table
            $nas = Nas::find($nas->id);
            if ($nas->id) {
                $router = new Router();
                $router->nas_id = $nas->id;
                $router->name = $nas->shortname;
                $router->ip_address = $staticIp;
                $router->type ='Radius';
                $router->secret = $nasSecret;
                $router->location = 'Dynamic';
                $router->created_by = Auth::user()->creatorId();
                $router->save();
            }
            // Step 4: Update FreeRADIUS Clients Configuration
            try {
                $serverIP = config('radius.server_ip');
                $serverPort = config('radius.server_port');
                $serverUser = config('radius.server_user');
                $serverPass = config('radius.server_pass');

                $ssh = new SSH2($serverIP, $serverPort);
                if (!$ssh->login($serverUser, $serverPass)) {
                    \Log::error("SSH login failed for FreeRADIUS server: $serverIP");
                    return redirect()->route('nas.index')->with('error', __('Unable to connect to FreeRADIUS server via SSH.'));
                }
                // if ($ssh->login($serverUser, $serverPass)) {
                    \Log::info("SSH login successful to FreeRADIUS server: $serverIP");

                    $cmds = [
                        "echo '$serverPass' | sudo -S service freeradius stop",
                        // "echo '$serverPass' | sudo -S chmod -R 777 /etc/freeradius",
                    ];
                    $cmds = [
                        "echo '$serverPass' | sudo -S service freeradius stop",
                        // "echo '$serverPass' | sudo -S chmod -R 777 /etc/freeradius",
                    ];
                    foreach ($cmds as $cmd) {
                        // $ssh->exec($cmd);
                        $output = $ssh->exec($cmd);
                        \Log::info("Executed: $cmd \nOutput: $output");
                    }

                    // Append new NAS entry
                    $config = "\n\n##############################################\n";
                    $config .= "client $staticIp {\n";
                    $config .= "\tipaddr = $staticIp\n\tsecret = $nasSecret\n}\n";
                    $config .= "##############################################\n";

                    $ssh->exec("echo '$serverPass' | sudo -S bash -c \"echo '$config' >> /etc/freeradius/clients.conf\"");

                    $cmds = [
                        // "echo '$serverPass' | sudo -S chmod -R 751 /etc/freeradius",
                        "echo '$serverPass' | sudo -S service freeradius restart",
                        "history -c && history -w",
                    ];
                    foreach ($cmds as $cmd) {
                        // $ssh->exec($cmd);
                        $output = $ssh->exec($cmd);
                        \Log::info("Executed: $cmd \nOutput: $output");
                    }
                // } else {
                //     return redirect()->route('nas.index')->with('error', __('Unable to update FreeRADIUS. Set NAS manually.'));
                // }
            } catch (\Exception $e) {
                return redirect()->route('nas.index')->with('error', __('Error updating FreeRADIUS: ') . $e->getMessage());
            }

            return redirect()->route('nas.index')->with('success', __('Site successfully created with VPN and FreeRADIUS.'));
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($ids)
    {
        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Site Not Found.'));
        }
        $id = \Crypt::decrypt($ids);
        // Retrieve NAS with linked routers and their assigned packages
        $nas = Nas::with('routers.packages.bandwidth')->findOrFail($id);

        // Check NAS online status
        $nas->status = $this->isNasOnline($nas->nasname) ? 'Online' : 'Offline';
        $routerIds = $nas->routers->pluck('id')->toArray();
        // Fetch all available packages (for assignment)
        // $packages = Package::where('created_by', \Auth::user()->creatorId())->get();;
        $packages = Package::leftJoin('router_packages', function ($join) use ($routerIds) {
            $join->on('packages.id', '=', 'router_packages.package_id')
                 ->whereIn('router_packages.router_id', $routerIds);
        })
        ->where('packages.created_by', \Auth::user()->creatorId())
        ->select('packages.*', 'router_packages.router_id as assigned_router_id')
        ->get();

        return view('nas.show', compact('nas', 'packages'));
    }


    public function edit(Nas $nas)
    {
        if (\Auth::user()->can('edit nas')) {
            // $nas = Nas::find($id);
            return view('nas.edit', compact('nas'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Nas $nas)
    {
        if (\Auth::user()->can('edit nas')) {
            $rules = [
                'site_name' => 'required|string|max:255',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('nas.index')->with('error', $messages->first());
            }

            // $nas = Nas::findOrFail($id);
            $nas->shortname = $request->site_name;
            $nas->save();

            return redirect()->route('nas.index')->with('success', __('Site successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Nas $nas)
    {
        if(\Auth::user()->can('delete nas'))
        {
            if($nas->created_by == \Auth::user()->creatorId())
            {
                $nas->delete();

                return redirect()->route('nas.index')->with('success', __('Site successfully deleted.'));
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
    public function assignPackage(Request $request, $nasId)
    {
        $request->validate([
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $router = Router::where('nas_id', $nasId)->firstOrFail();

        $selectedPackages = $request->input('package_ids', []);

        $existingPackages = RouterPackage::where('router_id', $router->id)->pluck('package_id')->toArray();

        $packagesToRemove = array_diff($existingPackages, $selectedPackages);
        RouterPackage::where('router_id', $router->id)->whereIn('package_id', $packagesToRemove)->delete();

        foreach ($selectedPackages as $packageId) {
            if (!in_array($packageId, $existingPackages)) {
                RouterPackage::create([
                    'router_id' => $router->id,
                    'package_id' => $packageId,
                ]);
            }
        }
        return redirect()->route('nas.show', ['nas' => encrypt($nasId)])->with('success', __('Packages updated successfully.'));
    }

}
