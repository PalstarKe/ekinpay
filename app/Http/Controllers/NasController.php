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

        // Fetch all available packages (for assignment)
        $packages = Package::where('created_by', \Auth::user()->creatorId())->get();;

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
    public function assignPackage(Request $request, $id)
    {
        $router = Router::where('nas_id', $id)->firstOrFail();

        $package = Package::findOrFail($request->package_id);

        if (!RouterPackage::where('router_id', $router->id)->where('package_id', $package->id)->exists()) {
            RouterPackage::create([
                'router_id' => $router->id,
                'package_id' => $package->id
            ]);
            // Assign package to RADIUS
            $planName = $package->name_plan;
            
            $timeout = '60';

            $timelimit = 0;

            if ($package->validity_unit == 'Minutes') {
                $timelimit = $package->validity * 60;
            } elseif ($package->validity_unit == 'Hours') {
                $timelimit = $package->validity * 3600;
            } elseif ($package->validity_unit == 'Days') {
                $timelimit = $package->validity * 86400;
            } elseif ($package->validity_unit == 'Months') {
                $timelimit = $package->validity * 2592000; // Assuming 30 days per month
            }
            $planTimeBank = $timelimit;

            $shared = $package->shared_users;

            function convertBandwidth($rate, $unit) {
                $multipliers = [
                    'K' => 1000,  // Kilobits to bits
                    'M' => 1000000, // Megabits to bits
                    'G' => 1000000000 // Gigabits to bits (if needed)
                ];
                return isset($multipliers[$unit]) ? ($rate * $multipliers[$unit]) : $rate;
            }
            
            $down = convertBandwidth($package->bandwidth->rate_down, $package->bandwidth->rate_down_unit);
            $up = convertBandwidth($package->bandwidth->rate_up, $package->bandwidth->rate_up_unit);

            $downm = $package->bandwidth->rate_down . $package->bandwidth->rate_down_unit;
            $upm = $package->bandwidth->rate_up . $package->bandwidth->rate_up_unit;
            $MikroRate = $downm . "/" . $upm;
            
            function convertDataLimit($data, $unit) {
                $multipliers = [
                    'K' => 1024, 
                    'M' => 1048576, 
                    'G' => 1073741824
                ];
                return isset($multipliers[$unit]) ? ($data * $multipliers[$unit]) : 0;
            }

            $datalimit = convertDataLimit($package->data_limit, $package->data_limit_unit);

            $bw_name = $package->bandwidth->name_plan;
            $bw_id = $package->bandwidth->id;

            $group_name = 'package_' . $package->id;
            $profileType = $package->typebp;
            $limitType = $package->limit_type;

            if ($profileType === 'Unlimited') {
                unset($datalimit); 
            } elseif ($profileType === 'Limited') {
                if ($limitType === 'Time_Limit') {
                    unset($datalimit);
                } elseif ($limitType === 'Data_Limit') {
                    unset($planTimeBank, $timelimit);
                }
            }
         

            DB::beginTransaction();

            try {
                // Insert into radgroupcheck
                $planCheckData = [
                    ['groupname' => $group_name, 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept']
                ];

                if (!empty($planTimeBank)) {
                    $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Session-Timeout', 'op' => ':=', 'value' => $planTimeBank];
                }
                if (!empty($datalimit)) {
                    $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Max-Octets', 'op' => ':=', 'value' => $datalimit];
                }
                if (!empty($shared)) {
                    $planCheckData[] = ['groupname' => $group_name, 'attribute' => 'Simultaneous-Use', 'op' => ':=', 'value' => $shared];
                }

                if (!empty($planCheckData)) {
                    DB::connection('radius')->table('radgroupcheck')->insert($planCheckData);
                }
                $planCheckExists = DB::connection('radius')->table('radgroupcheck')
                    ->where('groupname', 'Expired_Plan')
                    ->exists();

                if (!$planCheckExists) {
                    DB::connection('radius')->table('radgroupcheck')->insert([
                        ['groupname' => 'Expired_Plan', 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Accept'],
                        // ['groupname' => 'Expired_Plan', 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => '300'] // 5 min idle timeout
                    ]);
                }

                // Insert into radgroupreply
                $planReplyData = [];
                 
                if (!empty( $MikroRate)) {
                    $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' =>  $MikroRate];
                }

                if (!empty($down)) {
                    $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => $down];
                }
               
                if (!empty($up)) {
                    $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => $up];
                }

                // if (!empty($timeout)) {
                //     $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => $timeout];
                // }

                // Acct-Interim-Interval is always added
                $planReplyData[] = ['groupname' => $group_name, 'attribute' => 'Acct-Interim-Interval', 'op' => ':=', 'value' => '60'];

                if (!empty($planReplyData)) {
                    DB::connection('radius')->table('radgroupreply')->insert($planReplyData);
                }
                $planExists = DB::connection('radius')->table('radgroupreply')
                    ->where('groupname', 'Expired_Plan')
                    ->exists();

                if (!$planExists) {
                    DB::connection('radius')->table('radgroupreply')->insert([
                        ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '256K/256K'],
                        ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Down', 'op' => ':=', 'value' => '256000'],
                        ['groupname' => 'Expired_Plan', 'attribute' => 'WISPr-Bandwidth-Max-Up', 'op' => ':=', 'value' => '256000'],
                        ['groupname' => 'Expired_Plan', 'attribute' => 'Idle-Timeout', 'op' => ':=', 'value' => '300'],
                        ['groupname' => 'Expired_Plan', 'attribute' => 'Mikrotik-Address-List', 'op' => ':=', 'value' => 'EXPIRED_POOL']
                    ]);
                }


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
            return redirect()->route('nas.show', ['nas' => encrypt($id)])->with('success', __('Package assigned successfully.'));
        }
        return redirect()->route('nas.show', ['nas' => encrypt($id)])->with('success', __('Package already assigned.'));

    }

}
