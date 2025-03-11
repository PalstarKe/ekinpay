<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePackage extends Model
{
    protected $fillable = [
        'package_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function package(){
        return $this->hasOne('App\Models\Package', 'id', 'package_id');
    }    

  
}
