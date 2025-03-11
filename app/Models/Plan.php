<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        // 'max_users',
        'max_customers',
        'trial',
        'trial_days',
        'description',
        'chatgpt',
    ];

    private static $getplans = NULL;

    public static $arrDuration = [
        'lifetime' => 'Lifetime',
        'customer' => 'Customer',
        'month' => 'Per Month',
        'year' => 'Per Year',
    ];

    public function status()
    {
        return [
            __('lifetime'),
            __('customer'),
            __('Per Month'),
            __('Per Year'),
        ];
    }

    public static function total_plan()
    {
        return Plan::count();
    }

    public static function most_purchese_plan()
    {
        $free_plan = Plan::where('price', '<=', 0)->first()->id;
        $plan =  User::select(DB::raw('count(*) as total') , 'plan')->where('type', '=', 'company')->where('plan', '!=', $free_plan)->groupBy('plan')->first();

        return $plan;
    }

    public static function getPlan($id)
    {
        if(self::$getplans == null)
        {
            $plan = Plan::find($id);
            self::$getplans = $plan;
        }

        return self::$getplans;
    }
}
