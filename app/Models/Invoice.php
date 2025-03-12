<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'issue_date',
        'due_date',
        'ref_number',
        'status',
        'category',
        'created_by',
    ];

    public static $statues = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partialy Paid',
        'Paid',
    ];


    public function tax()
    {
        return $this->hasOne('App\Models\Tax', 'id', 'tax_id');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\InvoicePackage', 'invoice_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\InvoicePayment', 'invoice_id', 'id');
    }

    // public function bankPayments()
    // {
    //     return $this->hasMany('App\Models\InvoiceBankTransfer', 'invoice_id', 'id')->where('status', '!=', 'Approved');
    // }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'customer_id');
    }

    public function lastPayments()
    {
        return $this->hasOne('App\Models\InvoicePayment', 'invoice_id', 'id')->latest('created_at');
    }

    private static $overallTotal = null;
    public static function getOverallTotal()
    {
        if (self::$overallTotal === null) {
            $invoice = new self();
            self::$overallTotal = $invoice->invoiceTotal();
        }
        return self::$overallTotal;
    }

    public function getTotal()
    {
        return ($this->getSubTotal() - $this->getTotalDiscount()) + $this->getTotalTax();
    }

    public function getSubTotal()
    {
        $subTotal = 0;
        foreach ($this->packages as $package) {
            $subTotal += ($package->price * $package->quantity);
        }
        return $subTotal;
    }

    public function getTotalTax()
    {
        $taxData = Utility::getTaxData();
        $totalTax = 0;
        foreach ($this->packages as $package) {
            $taxArr = explode(',', $package->tax);
            $taxes = 0;
            foreach ($taxArr as $tax) {
                $taxes += !empty($taxData[$tax]['rate']) ? $taxData[$tax]['rate'] : 0;
            }
            $totalTax += ($taxes / 100) * ($package->price * $package->quantity);
        }
        return $totalTax;
    }

    public function getTotalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->packages as $package) {
            $totalDiscount += $package->discount;
        }
        return $totalDiscount;
    }

    public function getDue()
    {
        $due = 0;
        foreach ($this->payments as $payment) {
            $due += $payment->amount;
        }
        return $this->getTotal() - $due;
    }

    public static function changeStatus($invoice_id, $status)
    {
        $invoice = self::find($invoice_id);
        $invoice->status = $status;
        $invoice->update();
    }
}
