<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use Carbon\Carbon;

class SessionValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el token de sesión desde la URL, header o parámetro
        $sessionToken = $request->route('session_token') 
                       ?? $request->header('X-Session-Token') 
                       ?? $request->input('session_token');

        if (!$sessionToken) {
            return $this->unauthorizedResponse('Token de sesión requerido');
        }

        // Buscar la sesión en la base de datos
        $session = Session::where('token', $sessionToken)->first();

        if (!$session) {
            return $this->unauthorizedResponse('Sesión no encontrada');
        }

        // Verificar si la sesión ha expirado
        if ($this->isSessionExpired($session)) {
            // Marcar la sesión como expirada
            $session->update([
                'status' => 'expired',
                'expired_at' => Carbon::now()
            ]);

            return $this->unauthorizedResponse('Sesión expirada');
        }

        // Verificar el estado de la sesión
        if (!$this->isSessionValid($session)) {
            return $this->unauthorizedResponse('Sesión inválida o inactiva');
        }

        // Actualizar la última actividad de la sesión
        $this->updateSessionActivity($session);

        // Agregar la sesión al request para uso posterior
        $request->merge(['session' => $session]);
        $request->attributes->set('session', $session);

        return $next($request);
    }

    /**
     * Verificar si la sesión ha expirado
     */
    private function isSessionExpired(Session $session): bool
    {
        if (!$session->expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($session->expires_at);
    }

    /**
     * Verificar si la sesión es válida
     */
    private function isSessionValid(Session $session): bool
    {
        $validStatuses = ['active', 'uploading', 'configuring', 'ready', 'printing', 'logged'];
        return in_array($session->status, $validStatuses);
    }

    /**
     * Actualizar la última actividad de la sesión
     */
    private function updateSessionActivity(Session $session): void
    {
        $session->update([
            'last_activity' => Carbon::now()
        ]);
    }

    /**
     * Respuesta de error no autorizado
     */
    private function unauthorizedResponse(string $message): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'SESSION_INVALID'
            ], 401);
        }

        return redirect()->route('home')->with('error', $message);
    }
}