<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough; // <-- Ajout de l'import pour le hasManyThrough
use App\Traits\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\LocationScopable;

class User extends Authenticatable
{
    use Auditable;
    use HasFactory, Notifiable, LocationScopable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'username',
        'password',
        'role',
        'activity_zone',
        'activity_country',
        'is_active',
        'last_login_at',
        'can_validate_contributions',
        'can_manage_collectors',
        'can_view_audit_logs',
        'can_manage_lots',
        'can_manage_locations',
        'can_manage_backups',
        'country_id',
        'region_id',
        'zone_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'can_validate_contributions' => 'boolean',
            'can_manage_collectors' => 'boolean',
            'can_view_audit_logs' => 'boolean',
            'can_manage_lots' => 'boolean',
            'can_manage_locations' => 'boolean',
            'can_manage_backups' => 'boolean',
        ];
    }

    /**
     * Role checking helper functions.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
    }

    public function isCollector(): bool
    {
        return $this->role === 'collector';
    }

    /**
     * Check if user has permission.
     * Admins have all permissions.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return (bool) ($this->{$permission} ?? false);
    }

    /**
     * Relationship with Clients (registered by this user).
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'collector_id');
    }

    /**
     * Relationship with Contributions collected by this user.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'collector_id');
    }

    /**
     * Relationship with Contributions validated by this user (admin).
     */
    public function validatedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'validated_by');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Relationship with Audit Logs.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Relationship avec les Carnets vendus / assignés via ses clients.
     * (Permet à $collector->withCount('carnets') de fonctionner directement)
     */
    public function carnets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Carnet::class,       // Le modèle final qu'on veut récupérer
            Client::class,       // Le modèle intermédiaire
            'collector_id',      // Clé étrangère sur la table 'clients'
            'client_id',         // Clé étrangère sur la table 'carnets'
            'id',                // Clé locale sur la table 'users'
            'id'                 // Clé locale sur la table 'clients'
        );
    }

use LogsActivity;



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
