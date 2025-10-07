<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;
use Carbon\Carbon;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si hay una sesión administrativa activa
        $adminId = Session::get('admin_id');
        $adminToken = Session::get('admin_token');

        if (!$adminId || !$adminToken) {
            return $this->redirectToLogin('Sesión administrativa requerida');
        }

        // Buscar el administrador en la base de datos
        $admin = Admin::find($adminId);

        if (!$admin) {
            Session::flush();
            return $this->redirectToLogin('Administrador no encontrado');
        }

        // Verificar si el administrador está activo
        if (!$admin->is_active) {
            Session::flush();
            return $this->redirectToLogin('Cuenta de administrador desactivada');
        }

        // Verificar el token de sesión
        if (!$this->isValidToken($admin, $adminToken)) {
            Session::flush();
            return $this->redirectToLogin('Token de sesión inválido');
        }

        // Verificar si la sesión ha expirado
        if ($this->isSessionExpired()) {
            Session::flush();
            return $this->redirectToLogin('Sesión expirada');
        }

        // Actualizar la última actividad del administrador
        $this->updateAdminActivity($admin);

        // Renovar el tiempo de expiración de la sesión
        $this->renewSessionExpiration();

        // Agregar el administrador al request
        $request->merge(['admin' => $admin]);
        $request->attributes->set('admin', $admin);

        return $next($request);
    }

    /**
     * Verificar si el token es válido
     */
    private function isValidToken(Admin $admin, string $token): bool
    {
        // Generar el token esperado basado en el ID del admin y datos de sesión
        $expectedToken = hash('sha256', $admin->_id . $admin->email . Session::getId());
        return hash_equals($expectedToken, $token);
    }

    /**
     * Verificar si la sesión ha expirado
     */
    private function isSessionExpired(): bool
    {
        $lastActivity = Session::get('admin_last_activity');
        
        if (!$lastActivity) {
            return true;
        }

        $expirationTime = config('session.lifetime', 120); // minutos
        $lastActivityTime = Carbon::parse($lastActivity);
        
        return $lastActivityTime->addMinutes($expirationTime)->isPast();
    }

    /**
     * Actualizar la última actividad del administrador
     */
    private function updateAdminActivity(Admin $admin): void
    {
        $admin->update([
            'last_login' => Carbon::now(),
            'last_activity' => Carbon::now()
        ]);
    }

    /**
     * Renovar el tiempo de expiración de la sesión
     */
    private function renewSessionExpiration(): void
    {
        Session::put('admin_last_activity', Carbon::now()->toISOString());
    }

    /**
     * Redireccionar al login administrativo
     */
    private function redirectToLogin(string $message): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'ADMIN_AUTH_REQUIRED',
                'redirect' => route('admin.login')
            ], 401);
        }

        return redirect()->route('admin.login')->with('error', $message);
    }

    /**
     * Middleware para verificar permisos específicos
     */
    public static function checkPermission(string $permission)
    {
        return function (Request $request, Closure $next) use ($permission) {
            $admin = $request->attributes->get('admin');
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Administrador no autenticado'
                ], 401);
            }

            // Verificar si el admin tiene el permiso requerido
            if (!$admin->hasPermission($permission)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permisos insuficientes'
                ], 403);
            }

            return $next($request);
        };
    }

    /**
     * Middleware para verificar rol de super administrador
     */
    public static function requireSuperAdmin()
    {
        return function (Request $request, Closure $next) {
            $admin = $request->attributes->get('admin');
            
            if (!$admin || $admin->role !== 'super_admin') {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Se requieren permisos de super administrador'
                    ], 403);
                }

                return redirect()->route('admin.dashboard')->with('error', 'Acceso denegado');
            }

            return $next($request);
        };
    }
}