<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, Notifiable;
    protected $guard_name = 'web';

    protected $fillable = [
        'tech_id',
        'username',
        'email',
        'phone',
        'pin_code',
        'otp',
        'password',
        'type',
        'image',
        'role',
        'status',
        'fcm_token',
        'warehouse_id',
        'personnel_number',
        'technician_rec_id',
        'account_number',
        'address',
        'main_warehouse_id',
    ];


    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
        'otp'
    ];



    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id');
    }


    // user logs

    public function logs()
    {
        return $this->hasMany(UserLog::class);
    }
    // user pin reset requests
    public function pinResetRequests()
    {
        return $this->hasMany(PinResetRequest::class);
    }


    // technician logs
    public function technicianLogs()
    {
        return $this->hasMany(TechnicianLog::class, 'tech_id', 'tech_id');
    }



    // tech notifications
    public function techNotifications()
    {
        return $this->hasMany(TechNotification::class);
    }

    // tasks assigned to this user (Task management module)
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
}
