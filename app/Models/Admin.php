<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Admin extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'last_login',
        'login_attempts',
        'locked_until',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
        'last_login' => 'datetime',
        'locked_until' => 'datetime',
        'login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Roles de administrador
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_OPERATOR = 'operator';
    const ROLE_VIEWER = 'viewer';

    // Permisos disponibles
    const PERMISSION_MANAGE_PRINTERS = 'manage_printers';
    const PERMISSION_MANAGE_USERS = 'manage_users';
    const PERMISSION_VIEW_STATS = 'view_stats';
    const PERMISSION_MANAGE_SETTINGS = 'manage_settings';
    const PERMISSION_MANAGE_FILES = 'manage_files';
    const PERMISSION_SYSTEM_MAINTENANCE = 'system_maintenance';

    /**
     * Verificar si el administrador está activo
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Verificar si la cuenta está bloqueada
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until > Carbon::now();
    }

    /**
     * Bloquear cuenta por intentos fallidos
     */
    public function lockAccount($minutes = 30)
    {
        $this->update([
            'locked_until' => Carbon::now()->addMinutes($minutes)
        ]);
    }

    /**
     * Desbloquear cuenta
     */
    public function unlockAccount()
    {
        $this->update([
            'locked_until' => null,
            'login_attempts' => 0
        ]);
    }

    /**
     * Incrementar intentos de login
     */
    public function incrementLoginAttempts()
    {
        $attempts = $this->login_attempts + 1;
        $this->update(['login_attempts' => $attempts]);

        // Bloquear después de 5 intentos fallidos
        if ($attempts >= 5) {
            $this->lockAccount();
        }
    }

    /**
     * Resetear intentos de login
     */
    public function resetLoginAttempts()
    {
        $this->update(['login_attempts' => 0]);
    }

    /**
     * Registrar login exitoso
     */
    public function recordLogin()
    {
        $this->update([
            'last_login' => Carbon::now(),
            'login_attempts' => 0
        ]);
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public function hasPermission($permission)
    {
        if ($this->role === self::ROLE_SUPER_ADMIN) {
            return true; // Super admin tiene todos los permisos
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Verificar si tiene múltiples permisos
     */
    public function hasPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar si tiene al menos uno de los permisos
     */
    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Agregar permiso
     */
    public function grantPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remover permiso
     */
    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, function($p) use ($permission) {
            return $p !== $permission;
        });
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Verificar si es super administrador
     */
    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin()
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    /**
     * Verificar si es operador
     */
    public function isOperator()
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    /**
     * Verificar si es solo visualizador
     */
    public function isViewer()
    {
        return $this->role === self::ROLE_VIEWER;
    }

    /**
     * Obtener permisos por rol
     */
    public static function getPermissionsByRole($role)
    {
        $permissions = [
            self::ROLE_SUPER_ADMIN => [
                self::PERMISSION_MANAGE_PRINTERS,
                self::PERMISSION_MANAGE_USERS,
                self::PERMISSION_VIEW_STATS,
                self::PERMISSION_MANAGE_SETTINGS,
                self::PERMISSION_MANAGE_FILES,
                self::PERMISSION_SYSTEM_MAINTENANCE
            ],
            self::ROLE_ADMIN => [
                self::PERMISSION_MANAGE_PRINTERS,
                self::PERMISSION_VIEW_STATS,
                self::PERMISSION_MANAGE_SETTINGS,
                self::PERMISSION_MANAGE_FILES
            ],
            self::ROLE_OPERATOR => [
                self::PERMISSION_MANAGE_PRINTERS,
                self::PERMISSION_VIEW_STATS,
                self::PERMISSION_MANAGE_FILES
            ],
            self::ROLE_VIEWER => [
                self::PERMISSION_VIEW_STATS
            ]
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Obtener nombre del rol
     */
    public function getRoleName()
    {
        $roleNames = [
            self::ROLE_SUPER_ADMIN => 'Super Administrador',
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_OPERATOR => 'Operador',
            self::ROLE_VIEWER => 'Visualizador'
        ];

        return $roleNames[$this->role] ?? 'Desconocido';
    }

    /**
     * Crear administrador por defecto
     */
    public static function createDefaultAdmin()
    {
        return self::create([
            'name' => config('app.admin_name', 'Administrador Sistema'),
            'email' => config('app.admin_email', 'admin@imprimeindo.com'),
            'password' => Hash::make(config('app.admin_password', 'admin123')),
            'role' => self::ROLE_SUPER_ADMIN,
            'permissions' => self::getPermissionsByRole(self::ROLE_SUPER_ADMIN),
            'is_active' => true,
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * Mutator para encriptar password
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Verificar password
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Scope para administradores activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para administradores no bloqueados
     */
    public function scopeNotLocked($query)
    {
        return $query->where(function($q) {
            $q->whereNull('locked_until')
              ->orWhere('locked_until', '<=', Carbon::now());
        });
    }

    /**
     * Scope por rol
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope con permiso específico
     */
    public function scopeWithPermission($query, $permission)
    {
        return $query->where(function($q) use ($permission) {
            $q->where('role', self::ROLE_SUPER_ADMIN)
              ->orWhere('permissions', 'like', '%' . $permission . '%');
        });
    }

    /**
     * Obtener estadísticas de administradores
     */
    public static function getStats()
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'locked' => self::where('locked_until', '>', Carbon::now())->count(),
            'by_role' => [
                'super_admin' => self::byRole(self::ROLE_SUPER_ADMIN)->count(),
                'admin' => self::byRole(self::ROLE_ADMIN)->count(),
                'operator' => self::byRole(self::ROLE_OPERATOR)->count(),
                'viewer' => self::byRole(self::ROLE_VIEWER)->count()
            ]
        ];
    }
}