<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pin extends Eloquent
{
    use LogsActivity;

    protected $fillable = ['code', 'user_id', 'student_id', 'usage_count', 'is_used'];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function user($foreign = null)
    {
        return $this->belongsTo(User::class, $foreign);
    }

    public function student()
    {
        return $this->user('student_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
