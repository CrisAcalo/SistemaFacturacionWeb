<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'email_verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Log cuando el usuario inicia sesión
     */
    public function logLogin(): void
    {
        activity('login')
            ->causedBy($this)
            ->performedOn($this)
            ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
            ->log('Usuario inició sesión');
    }

    /**
     * Log cuando el usuario cierra sesión
     */
    public function logLogout(): void
    {
        activity('logout')
            ->causedBy($this)
            ->performedOn($this)
            ->withProperties(['ip' => request()->ip()])
            ->log('Usuario cerró sesión');
    }

    /**
     * Log cuando un usuario es restaurado
     */
    public function logRestored(string $reason = null): void
    {
        activity('restored')
            ->causedBy(auth()->user())
            ->performedOn($this)
            ->withProperties(['reason' => $reason ?? 'No se proporcionó motivo'])
            ->log('Usuario restaurado');
    }

    /**
     * Log cuando un usuario es eliminado definitivamente
     */
    public function logForceDeleted(string $reason = null): void
    {
        activity('forceDeleted')
            ->causedBy(auth()->user())
            ->performedOn($this)
            ->withProperties(['reason' => $reason ?? 'No se proporcionó motivo'])
            ->log('Usuario eliminado definitivamente');
    }
}
