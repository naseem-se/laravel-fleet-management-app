<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'email', 'phone', 'password', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function isSuperAdmin(): bool
    {
        return is_null($this->company_id) && $this->hasRole('super_admin');
    }

    public function sendEmailVerificationNotification(): void
    {
        $frontendUrl = $this->hasRole('driver')
            ? config('app.driver_frontend_url')
            : ($this->company_id === null ? config('app.super_admin_frontend_url') : config('app.admin_frontend_url'));

        $this->notify(new \App\Notifications\VerifyEmailNotification($frontendUrl));
    }
}