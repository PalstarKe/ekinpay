<?php

namespace App\Http\Controllers;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\SmsAlert;
use App\Models\SmsDelivered;
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
use Illuminate\Support\Facades\Log;


class SmsController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage sms template')) {

            $smsTemplates = SmsAlert::where('created_by', Auth::user()->creatorId())->get();
            return view('sms.index', compact('smsTemplates'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create sms template'))
        {
            return view('sms.create');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create sms template')) {
            $rules = [
                'type'      => ['required',
                    Rule::unique('smsalerts', 'type')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    })
                ],
                'template'      => 'required',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('sms.index')->with('error', $messages->first());
            }

            $sms = new SmsAlert();
            $sms->type = $request->type;
            $sms->template = $request->template;
            $sms->status = 1;
            $sms->created_by = Auth::user()->id;
            $sms->save();

            return redirect()->route('sms.index')->with('success', __('SMS Template Created Successfully.'));
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (!Auth::user()->can('edit sms template')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $sms = SmsAlert::findOrFail($id);

        return view('sms.edit', compact('sms'));
    }


    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('edit sms template')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $sms = SmsAlert::findOrFail($id);

        $request->validate([
            'type' => [
                'required',
                Rule::unique('smsalerts', 'type')->ignore($sms->id)->where(function ($query) {
                    return $query->where('created_by', Auth::id());
                })
            ],
            'template' => 'required',
        ]);

        $sms->update([
            'type' => $request->type,
            'template' => $request->template,
        ]);

        return redirect()->route('sms.index')->with('success', __('Template successfully updated.'));
    }


    public function destroy($id)
    {
        if (!Auth::user()->can('delete sms template')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $sms = SmsAlert::findOrFail($id);
        $sms->delete();

        return redirect()->route('sms.index')->with('success', __('Template deleted successfully.'));
    }

}
