<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\DealTask;
use App\Models\Router;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Pos;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Purchase;
use App\Models\Revenue;
use App\Models\Stage;
use App\Models\Tax;
use App\Models\Timesheet;
use App\Models\TimeTracker;
use App\Models\Package;
use App\Models\Training;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }


    public function landingPage()
    {
        if (!file_exists(storage_path() . "/installed")) {
            header('location:install');
            die;
        }

        $adminSettings = Utility::settings();
        if ($adminSettings['display_landing_page'] == 'on' && \Schema::hasTable('landing_page_settings')) {

            return view('landingpage::layouts.landingpage' , compact('adminSettings'));

        } else {
            return redirect('login');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show_dashboard()
    {
        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                return redirect()->route('client.dashboard.view');
            } elseif (Auth::user()->type == 'client') {
                return redirect()->route('client.dashboard.view');
            } else {
                if (\Auth::user()->can('show dashboard')) {
                    $data = [];
    
                    $data['latestIncome'] = Transaction::where('created_by', Auth::user()->creatorId())
                        ->orderBy('id', 'desc')
                        ->limit(5)
                        ->get();

                    $data['todayIncome'] = Transaction::where('created_by', Auth::user()->creatorId())
                        ->whereDate('created_at', Carbon::today())
                        ->sum('amount');
                    
    
                    $data['users'] = User::find(Auth::user()->creatorId());

                    $sites = Router::where('created_by', Auth::user()->creatorId())->get();
                    $data['totalSites'] = $sites->count();

                    $creatorId = Auth::user()->creatorId();
                    $totalCustomers = Customer::where('created_by', $creatorId)->count();
                    $activeCustomers = Customer::where('created_by', $creatorId)->where('expiry_status', 'on')->count();
                    $expiredCustomers = Customer::where('created_by', $creatorId)->where('expiry_status', 'off')->count();
                    $usernames = Customer::where('created_by', $creatorId)->pluck('username')->toArray();

                    $onlineTotal = DB::connection('radius')->table('radacct')->whereIn('username', $usernames)->whereNull('acctstoptime')->count();

                    $onlinePPP = DB::connection('radius')->table('radacct')->whereIn('username', $usernames)->whereNull('acctstoptime')->where('framedprotocol', 'PPP')->count();

                    $onlineHotspot = DB::connection('radius')->table('radacct')->whereIn('username', $usernames)->whereNull('acctstoptime')->where('framedprotocol', 'hotspot')->count();

                    $data['totalCustomers'] = $totalCustomers;
                    $data['activeCustomers'] = $activeCustomers;
                    $data['expiredCustomers'] = $expiredCustomers;
                    $data['onlineCustomers'] =$onlineTotal;
                    $data['onlinePPPoE'] = $onlinePPP;
                    $data['onlineHotspot'] = $onlineHotspot;

                    $data['activePercentage'] = $totalCustomers > 0 
                    ? round(($activeCustomers / $totalCustomers) * 100, 2) 
                    : 0;
                    $activePercentage = $totalCustomers > 0 
                        ? (int) (($activeCustomers / $totalCustomers) * 100) 
                        : 0; // Ensure it's an integer

                    $Actdata = [
                        'activePercentage' => $activePercentage
                    ];
                    $todayEntries = Transaction::where('created_by', $creatorId)->whereDate('created_at', Carbon::today())->count();
                    $data['todayEntries'] = $todayEntries;
                    $yesterdayEntries = Transaction::where('created_by', $creatorId)->whereDate('created_at', Carbon::yesterday())->count();
                    if ($yesterdayEntries == 0) {
                        $percentageChange = $todayEntries > 0 ? 100 : 0; // If no transactions yesterday but some today, 100% increase
                    } else {
                        $percentageChange = (($todayEntries - $yesterdayEntries) / $yesterdayEntries) * 100;
                    }
                    $data['percentageChangeEntries'] = round($percentageChange, 2);

                    $chartData = [
                        'labels' => [],
                        'data' => []
                    ];
                
                    for ($i = 5; $i >= 0; $i--) {
                        $date = Carbon::today()->subDays($i)->toDateString();
                        $count = Transaction::where('created_by', $creatorId)
                            ->whereDate('created_at', $date)
                            ->count();
                
                        $chartData['labels'][] = Carbon::today()->subDays($i)->format('d'); // Example: "Mar 03"
                        $chartData['data'][] = $count;
                    }
                    
                    $thisMonthIncome = Transaction::where('created_by', $creatorId)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('amount');

                    $lastMonthIncome = Transaction::where('created_by', $creatorId)
                    ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year)
                    ->sum('amount');

                    if ($lastMonthIncome > 0) {
                        $incomePercentageChange = round((($thisMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 2);
                    } else {
                        $incomePercentageChange = $thisMonthIncome > 0 ? 100 : 0;
                    }
                    
                    // Send data to the view
                    $data['thisMonthIncome'] = $thisMonthIncome;
                    $data['lastMonthIncome'] = $lastMonthIncome;
                    $data['incomePercentageChange'] = (int) $incomePercentageChange;

                    $thisMonthEntries = Transaction::where('created_by', $creatorId)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count();

                    $hotspotEntries = Transaction::where('created_by', $creatorId)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->where('type', 'Hotspot') 
                        ->count();

                    // Get entries for PPPoE
                    $pppoeEntries = Transaction::where('created_by', $creatorId)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->where('type', 'PPPoE')
                        ->count();

                    // $data['thisMonthEntries'] = $thisMonthEntries;
                    // $data['hotspotEntries'] = $hotspotEntries;
                    // $data['pppoeEntries'] = $pppoeEntries;
                    $Entdata = [
                        'pppoeEntries' => $pppoeEntries,
                        'hotspotEntries' => $hotspotEntries,
                        'thisMonthEntries' =>  $thisMonthEntries

                    ];
                    
                    $months = collect(range(0, 11))->map(function ($i) {
                        return Carbon::now()->subMonths($i)->format('M');
                    })->reverse()->values(); // Get last 12 months
                    
                    $revenues = [];
                    $expenses = [];
                    
                    foreach ($months as $monthIndex => $monthName) {
                        $startOfMonth = Carbon::now()->subMonths(11 - $monthIndex)->startOfMonth();
                        $endOfMonth = Carbon::now()->subMonths(11 - $monthIndex)->endOfMonth();
                    
                        // Calculate revenue
                        $revenue =Transaction::where('created_by', $creatorId)
                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->sum('amount');
                    
                        // Calculate expenses
                        $expense = Expense::where('created_by', $creatorId)
                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->sum('amount');
                    
                        $revenues[] = $revenue;
                        $expenses[] = -$expense;
                    }
                    
                    $expenData = [
                        'months' => $months,
                        'revenues' => $revenues,
                        'expenses' => $expenses
                    ];
                    $currentYear = Carbon::now()->year;

                    // Get total revenue for the current year
                    $totalRevenueYear = Transaction::where('created_by', $creatorId)
                        ->whereYear('created_at', $currentYear)
                        ->sum('amount');

                    $data['totalRevenueYear'] = $totalRevenueYear;$currentYear = Carbon::now()->year;

                    // Get total revenue for the current year
                    $totalRevenueYear = Transaction::where('created_by', $creatorId)
                        ->whereYear('created_at', $currentYear)
                        ->sum('amount');

                    $data['totalRevenueYear'] = $totalRevenueYear;

                    $today = Carbon::today();

                    $topPackages = Revenue::select('reference', DB::raw('COUNT(*) as total_sales'), DB::raw('SUM(amount) as total_revenue'))
                        ->where('created_by', $creatorId)
                        ->whereDate('created_at', $today)
                        ->groupBy('reference')
                        ->orderByDesc('total_sales')
                        ->limit(5)
                        ->get();
                    
                    $topUsers = DB::connection('radius')->table('radacct')
                        ->select('username', DB::raw('SUM(acctinputoctets + acctoutputoctets) AS total_data_usage'))
                        ->whereIn('username', $usernames)
                        ->whereNull('acctstoptime') // Ensure they are currently active
                        ->groupBy('username')
                        ->orderByDesc('total_data_usage')
                        ->limit(5)
                        ->get();

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
        

                    return view('dashboard.dashboard', compact('data', 'chartData', 'Actdata', 'Entdata', 'expenData', 'topPackages', 'topUsers', 'customerN', 'arrType', 'arrPackage'));
                }
            }
        }
        return redirect('login');
    }
    
    // Helper function to check NAS status
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

    public function getNasCounts()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sites = Router::where('created_by', Auth::user()->creatorId())->get();

        $onlineCount = 0;
        $offlineCount = 0;

        foreach ($sites as $site) {
            $nasIp = $site->ip_address;
            $status = $this->isNasOnline($nasIp);

            if ($status) {
                $onlineCount++;
            } else {
                $offlineCount++;
            }
        }

        return response()->json([
            'total' => $sites->count(),
            'online' => $onlineCount,
            'offline' => $offlineCount
        ]);
    }


    // Load Dashboard user's using ajax
    public function filterView(Request $request)
    {
        $usr = Auth::user();
        $users = User::where('id', '!=', $usr->id);

        if ($request->ajax()) {
            if (!empty($request->keyword)) {
                $users->where('name', 'LIKE', $request->keyword . '%')->orWhereRaw('FIND_IN_SET("' . $request->keyword . '",skills)');
            }

            $users = $users->get();
            $returnHTML = view('dashboard.view', compact('users'))->render();

            return response()->json([
                'success' => true,
                'html' => $returnHTML,
            ]);
        }
    }

    public function clientView()
    {

        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                $user = \Auth::user();
                $user['total_user'] = $user->countCompany();
                $user['total_paid_user'] = $user->countPaidCompany();
                $user['total_orders'] = Order::total_orders();
                $user['total_orders_price'] = Order::total_orders_price();
                $user['total_plan'] = Plan::total_plan();
                if(!empty(Plan::most_purchese_plan()))
                {
                    $plan = Plan::find(Plan::most_purchese_plan()['plan']);
                    $user['most_purchese_plan'] = $plan->name;
                }
                else
                {
                    $user['most_purchese_plan'] = '-';
                }

                $chartData = $this->getOrderChart(['duration' => 'week']);

                return view('dashboard.super_admin', compact('user', 'chartData'));

            } elseif (Auth::user()->type == 'client') {
                $transdate = date('Y-m-d', time());
                $currentYear = date('Y');

                $calenderTasks = [];
                $chartData = [];
                $arrCount = [];
                $arrErr = [];
                $m = date("m");
                $de = date("d");
                $y = date("Y");
                $format = 'Y-m-d';
                $user = \Auth::user();
                if (\Auth::user()->can('View Task')) {
                    $company_setting = Utility::settings();
                }
                $arrTemp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arrTemp['date'][] = __(date('D', strtotime($date)));
                    $arrTemp['invoice'][] = 10;
                    $arrTemp['payment'][] = 20;
                }

                $chartData = $arrTemp;

                foreach ($user->clientDeals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route('deals.tasks.show', [
                                $deal->id,
                                $task->id,
                            ]),
                            'className' => ($task->status) ? 'bg-primary border-primary' : 'bg-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $client_deal = $user->clientDeals->pluck('id');

                $arrCount['deal'] = !empty($user->clientDeals) ? $user->clientDeals->count() : 0;

                if (!empty($client_deal->first())) {

                    $arrCount['task'] = DealTask::whereIn('deal_id', [$client_deal->first()])->count();

                } else {
                    $arrCount['task'] = 0;
                }

                $project['projects'] = Project::where('client_id', '=', Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('end_date', '>', date('Y-m-d'))->limit(5)->orderBy('end_date')->get();
                $project['projects_count'] = count($project['projects']);
                $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();
                $tasks = ProjectTask::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_tasks_count'] = count($tasks);
                $project['project_budget'] = Project::where('client_id', Auth::user()->id)->sum('budget');

                $project_last_stages = Auth::user()->last_projectstage();
                $project_last_stage = (!empty($project_last_stages) ? $project_last_stages->id : 0);
                $project['total_project'] = Auth::user()->user_project();
                $total_project_task = Auth::user()->created_total_project_task();
                $allProject = Project::where('client_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allProjectCount = count($allProject);

                $bugs = Bug::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_bugs_count'] = count($bugs);
                $bug_last_stage = BugStatus::orderBy('order', 'DESC')->first();
                $completed_bugs = Bug::whereIn('project_id', $user_projects)->where('status', $bug_last_stage->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allBugCount = count($bugs);
                $completedBugCount = count($completed_bugs);
                $project['project_bug_percentage'] = ($allBugCount != 0) ? intval(($completedBugCount / $allBugCount) * 100) : 0;
                $complete_task = Auth::user()->project_complete_task($project_last_stage);
                $completed_project = Project::where('client_id', \Auth::user()->id)->where('status', 'complete')->where('created_by', \Auth::user()->creatorId())->get();
                $completed_project_count = count($completed_project);
                $project['project_percentage'] = ($allProjectCount != 0) ? intval(($completed_project_count / $allProjectCount) * 100) : 0;
                $project['project_task_percentage'] = ($total_project_task != 0) ? intval(($complete_task / $total_project_task) * 100) : 0;
                $invoice = [];
                $top_due_invoice = [];
                $invoice['total_invoice'] = 5;
                $complete_invoice = 0;
                $total_due_amount = 0;
                $top_due_invoice = array();
                $pay_amount = 0;

                if (Auth::user()->type == 'client') {
                    if (!empty($project['project_budget'])) {
                        $project['client_project_budget_due_per'] = intval(($pay_amount / $project['project_budget']) * 100);
                    } else {
                        $project['client_project_budget_due_per'] = 0;
                    }

                }

                $top_tasks = Auth::user()->created_top_due_task();
                $users['staff'] = User::where('created_by', '=', Auth::user()->creatorId())->count();
                $users['user'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'client')->count();
                $users['client'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'client')->count();
                $project_status = array_values(Project::$project_status);
                $projectData = \App\Models\Project::getProjectStatus();

                $taskData = \App\Models\TaskStage::getChartData();

                return view('dashboard.clientView', compact('calenderTasks', 'arrErr', 'arrCount', 'chartData', 'project', 'invoice', 'top_tasks', 'top_due_invoice', 'users', 'project_status', 'projectData', 'taskData', 'transdate', 'currentYear'));
            }
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-2 week +1 day");
                for ($i = 0; $i < 14; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask = [];
        $arrTask['label'] = [];
        $arrTask['data'] = [];
        foreach ($arrDuration as $date => $label) {

            $data = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][] = $data->total;
        }

        return $arrTask;
    }

}
