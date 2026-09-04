<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StaffRecord extends Eloquent
{
    use LogsActivity;

    protected $fillable = ['code', 'employment_date', 'employment_number', 'user_id', 'confirmation_date', 'licence_number', 'file_number', 'bank_account_number', 'tin_number', 'education_level', 'year_graduated', 'social_security_number', 'college_attended', 'primary_identification_number', 'secondary_identification_number', 'designation', 'date_of_birth', 'subjects_studied', 'bank_name', 'number_of_periods', 'lga_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
