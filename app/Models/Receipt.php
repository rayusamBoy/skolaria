<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Receipt extends Eloquent
{
    use LogsActivity;

    protected $fillable = ['payment_record_id', 'year', 'balance', 'amount_paid'];

    public function pr()
    {
        return $this->belongsTo(PaymentRecord::class, 'payment_record_id');
    }
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
