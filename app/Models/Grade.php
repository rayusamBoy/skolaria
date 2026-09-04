<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Grade extends Eloquent
{
    use LogsActivity;

    protected $fillable = ['name', 'class_type_id', 'mark_from', 'mark_to', 'remarks', 'point'];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
