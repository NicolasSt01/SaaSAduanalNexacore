<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Traits\BelongsToTenant;

class User extends Authenticatable implements MustVerifyEmail
{
    /* @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'cliente_id',
        'tenant_id',
        'active',
        'permisos',
        'active_session_id',
        'must_change_password',
        'password_changed_at',
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
            'role' => 'string',
            'permisos' => 'array',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }
    // Relación con clientes (para usuarios tipo cliente)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
        //return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Métodos para verificar roles
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAdminN2()
    {
        return $this->role === 'admin_n2';
    }

    public function isCliente()
    {
        return $this->role === 'cliente';
    }

    public function isDocumentador()
    {
        return $this->role === 'documentador';
    }
    public function scopeActivos($query)
    {
        return $query->where('active', true);
    }
    public function operaciones(): HasMany
    {
        return $this->hasMany(Operacion::class, 'usuario_cierre_id');
    }
    /*
     Relaciones para Notificaciones
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    public function notificacionesNoLeidas()
    {
        return $this->hasMany(Notificacion::class)->where('leida', false);
    }

    public function hasPermiso($permiso)
    {
        // 1. Verificar si el permiso está habilitado para el Tenant
        $tenant = $this->tenant;
        if ($tenant) {
            $allPermisos = self::getAllAvailablePermisos();
            $permisosHabilitadosByTenant = $tenant->configuracion['permisos'] ?? array_keys($allPermisos);
            if (!in_array($permiso, $permisosHabilitadosByTenant)) {
                return false; // El tenant no tiene contratado/habilitado este permiso
            }
        }

        // 2. Si el rol es admin, tiene acceso a todo lo habilitado para su tenant
        $role = strtolower($this->role);
        if ($role === 'admin' || $role === 'super_admin')
            return true;

        // 3. Verificar permisos individuales del usuario
        if (empty($this->permisos))
            return false;
        if (!is_array($this->permisos))
            return false;
        return in_array($permiso, $this->permisos);
    }

    public function hasAnyConfigPermiso()
    {
        if (strtolower($this->role) === 'admin' || strtolower($this->role) === 'super_admin')
            return true;

        $available = self::getAllAvailablePermisos();
        foreach (array_keys($available) as $permiso) {
            if ($this->hasPermiso($permiso))
                return true;
        }
        return false;
    }

    public static function getAllAvailablePermisos()
    {
        return [
            'gestionar_usuarios' => 'Gestionar Usuarios',
            'gestionar_patentes' => 'Gestionar Patentes',
            'gestionar_aduanas' => 'Gestionar Aduanas',
            'gestionar_clientes' => 'Gestionar Clientes',
            'gestionar_importadores' => 'Gestionar Importadores',
            'gestionar_bodegas' => 'Gestionar Bodegas',
            'gestionar_pedimentos' => 'Gestionar Pedimentos',
            'gestionar_referencias' => 'Gestionar Referencias',
            'gestionar_analiticas' => 'Gestionar Analíticas',
        ];
    }

    // ==========================================
    // MÉTODOS DE CAMBIO DE CONTRASEÑA
    // ==========================================

    /**
     * Verifica si el usuario debe cambiar su contraseña.
     */
    public function mustChangePassword(): bool
    {
        return $this->must_change_password ?? false;
    }

    /**
     * Marca la contraseña como cambiada.
     */
    public function markPasswordAsChanged(): void
    {
        $this->must_change_password = false;
        $this->password_changed_at = now();
        $this->save();
    }
}