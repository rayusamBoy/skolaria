<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Eloquent
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['title', 'amount', 'my_class_id', 'description', 'year', 'reference_number'];

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
