<?php

namespace App;

use App\Models\BloodGroup;
use App\Models\ExamRecord;
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
    use CanResetPassword, HasFactory, HasRoles, LogsActivity, Messagable, Notifiable, SoftDeletes;

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
        'secondary_mobile',
        'date_of_birth',
        'gender',
        'photo_path',
        'address',
        'blood_group_id',
        'password',
        'nationality_id',
        'state_id',
        'lga_id',
        'code',
        'email_verified_at',
        'occupation',
        'religion',
        'is_blocked',
        'primary_identification_number',
        'secondary_identification_number',
        'twofa_secret_code',
        'twofa_recovery_codes',
        'notification_preferences',
        'preferences',
        'message_media_heading_color',
        'firebase_device_tokens',
        'hidden_alert_ids',
        'password_updated_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'notification_preferences' => 'array',
        'preferences' => 'array',
        'firebase_device_tokens' => 'array',
        'hidden_alert_ids' => 'array',
        'twofa_recovery_codes' => 'array',
        'is_blocked' => 'boolean',
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

    public function exam_records()
    {
        return $this->hasMany(ExamRecord::class, 'student_id');
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
        return $this->belongsTo(Nationality::class);
    }

    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class);
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
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['password', 'password_updated_at', 'hidden_alert_ids']);
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
        return $this->primary_mobile ?? $this->secondary_mobile;
    }

    public function getByLatestLoginHistories()
    {
        return LoginHistory::where('user_id', auth()->id())->latest('logged_in_at')->get();
    }
}
