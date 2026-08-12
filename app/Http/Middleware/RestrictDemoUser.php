<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que restringe acciones destructivas o sensibles para la cuenta demo.
 * El usuario demo puede navegar y consultar libremente, pero no puede
 * eliminar datos, cambiar configuración del negocio ni enviar correos.
 */
class RestrictDemoUser
{
    /**
     * Email de la cuenta demo.
     */
    protected const DEMO_EMAIL = 'demo@impulweb.test';

    /**
     * Rutas (por nombre) que están completamente bloqueadas para el usuario demo.
     */
    protected array $blockedRoutes = [
        // Eliminar movimientos, gastos, inventario
        'movimiento.eliminar',
        'gastofijo.eliminar',
        'inventario.destroy',

        // Cambiar configuración del negocio
        'configuracion.actualizar',

        // Enviar correos reales
        'facturas.enviar',

        // Importar datos masivos
        'inventario.importar',

        // Reconstruir stock (operación peligrosa)
        'inventario.reconstruir',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado o no es el demo, pasar
        if (!$user || $user->email !== self::DEMO_EMAIL) {
            return $next($request);
        }

        // Verificar si la ruta actual está bloqueada
        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->blockedRoutes)) {
            // Para peticiones AJAX, responder con JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => '🔒 Esta acción no está disponible en la cuenta demo.'
                ], 403);
            }

            // Para peticiones normales, redirigir con mensaje
            return redirect()->back()->with('error', '🔒 Esta acción no está disponible en la cuenta demo. Es solo una demostración.');
        }

        return $next($request);
    }
}
