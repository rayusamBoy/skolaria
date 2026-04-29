<?php

namespace App;

use App\Models\BloodGroup;
use App\Models\Lga;
use App\Models\LoginHistory;
use App\Models\Nationality;
use App\Models\StaffRecord;
use App\Models\State;
use App\Models\StudentRecord;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, Messagable, CanResetPassword, LogsActivity, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'phone2',
        'dob',
        'gender',
        'photo',
        'address',
        'bg_id',
        'password',
        'nal_id',
        'state_id',
        'lga_id',
        'code',
        'email_verified_at',
        'work',
        'religion',
        'blocked',
        'primary_id',
        'secondary_id',
        'twofa_secret_code',
        'twofa_recovery_codes',
        'is_notifiable',
        'preferences',
        'message_media_heading_color',
        'firebase_device_tokens',
        'hidden_alert_ids',
        'password_updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_notifiable' => 'array',
        'preferences' => 'array',
        'firebase_device_tokens' => 'array',
        'hidden_alert_ids' => 'array',
        'twofa_recovery_codes' => 'array',
    ];

    public const string GENDER_MALE = 'Male';
    public const string GENDER_FEMALE = 'Female';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'twofa_secret_code',
        'twofa_recovery_codes',
        'firebase_device_tokens',
    ];

    public function student_record()
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nal_id');
    }

    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class, 'bg_id');
    }

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function getDeviceTokens()
    {
        return $this->firebase_device_tokens ?? [];
    }

    /**
     * Specifies the user's FCM tokens
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->getDeviceTokens();
    }

    /**
     * Route notifications for the Vonage channel.
     */
    public function routeNotificationForVonage(): string
    {
        return $this->phone ?? $this->phone2;
    }

    public function loginHistory()
    {
        $hist = LoginHistory::where('user_id', auth()->id())->get();
        return $hist;
    }
}
