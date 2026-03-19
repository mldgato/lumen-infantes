<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificamos si hay un usuario logueado y si debe cambiar su contraseña
        if (Auth::check() && Auth::user()->must_change_password) {

            // Permitimos que el usuario acceda a la ruta de cambiar contraseña 
            // y a la ruta de logout para evitar un bucle de redirecciones infinitas.
            if (!$request->routeIs('password.force-change') && !$request->routeIs('logout')) {
                return redirect()->route('password.force-change');
            }
        }

        return $next($request);
    }
}
