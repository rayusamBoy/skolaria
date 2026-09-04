<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentRecord extends Eloquent
{
    use LogsActivity;

    protected $fillable =['student_id', 'payment_id', 'amount_paid', 'year', 'is_paid', 'balance', 'reference_number'];

    protected $casts = [
        'is_paid' => 'boolean',
    ];
    
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function receipt()
    {
        return $this->hasMany(Receipt::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
