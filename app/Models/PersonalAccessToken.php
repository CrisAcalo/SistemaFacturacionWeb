<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'token',
        'plain_text_token',
        'abilities',
        'description',
        'is_active',
        'metadata',
        'expires_at',
        'created_by_role',
    ];

    protected $casts = [
        'abilities' => 'json',
        'metadata' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'is_active', 'expires_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Determinar si el token está activo
     */
    public function isActive(): bool
    {
        return $this->is_active && ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * Desactivar el token
     */
    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        activity('token_deactivated')
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Token desactivado');

        return $this;
    }

    /**
     * Activar el token
     */
    public function activate(): self
    {
        $this->update(['is_active' => true]);

        activity('token_activated')
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Token activado');

        return $this;
    }

    /**
     * Verificar si el token ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    /**
     * Scope para tokens activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope para tokens inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para tokens expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Obtener el usuario que creó el token
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'tokenable_id');
    }

    /**
     * Obtener información de estado del token
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Obtener el color del estado para la UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'red',
            'expired' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Obtener texto legible del estado
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'expired' => 'Expirado',
            default => 'Desconocido'
        };
    }
}
