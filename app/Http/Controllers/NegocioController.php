<?php
// Controlador principal que gestiona las métricas, proyecciones y estado financiero general del dashboard.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Item;
use App\Models\Negocio;
use App\Models\GastoFijo;
use App\Models\MetaMensual;
use App\Models\VentaDetalle;
use App\Models\MovimientoCaja;
use App\Models\ConfigEstrategica;
use App\Services\FinanzasService;
use App\Http\Requests\GuardarConfiguracionRequest;
use App\Services\DashboardService;

class NegocioController extends Controller
{
    // =====================================================
    // CONFIGURACIÓN INICIAL
    // =====================================================
    public function showConfiguracion()
    {
        return view('configuracion-inicial');
    }

    public function guardarConfiguracion(GuardarConfiguracionRequest $request)
    {
        $promedioVentas    = ((float)$request->ventas_mes1 + (float)$request->ventas_mes2 + (float)$request->ventas_mes3) / 3;
        $gastosFijosReales = ((float)($request->servicios_mes ?? 0))
                           + ((float)($request->renta_local ?? 0))
                           + ((float)($request->otros_gastos_fijos ?? 0));
        $nominaActual      = (float)($request->nomina_empleados ?? 0);
        $sueldoActual      = (float)$request->sueldo_dueno;
        $tipoNegocio       = $request->tipo_negocio;

        $margenOperacional  = $tipoNegocio === 'servicios' ? (float)($request->margen_operacional ?? 0) : 0;
        $margenContribucion = $tipoNegocio === 'servicios' ? 1 : ($margenOperacional > 0 ? ($margenOperacional / 100) / (1 + ($margenOperacional / 100)) : 0);
        $peCalculado        = $margenContribucion > 0 ? ($gastosFijosReales + $nominaActual + $sueldoActual) / $margenContribucion : 0;

        // Advertencia solo para servicios
        if ($tipoNegocio === 'servicios' && $peCalculado > $promedioVentas && !$request->ignorar_advertencia) {
            $reinversionIngresada    = (float)$request->utilidad_ahorro_reinversion;
            $topeGastos              = $promedioVentas * $margenContribucion * 0.60;
            $gastosFijosRecomendados = $gastosFijosReales > $topeGastos ? $topeGastos : $gastosFijosReales;
            $utilidadDisponible      = ($promedioVentas * $margenContribucion) - $gastosFijosRecomendados;
            $mitad                   = $utilidadDisponible * 0.50;
            $reinversionRecomendada  = $reinversionIngresada > $mitad ? $mitad : $reinversionIngresada;
            $sueldoRecomendado       = $reinversionIngresada > $mitad ? $mitad : $utilidadDisponible - $reinversionIngresada;

            return back()
                ->with('advertencia_financiera', true)
                ->with('pe_calculado', $peCalculado)
                ->with('promedio_ventas', $promedioVentas)
                ->with('sueldo_recomendado', $sueldoRecomendado)
                ->with('gastos_recomendados', $gastosFijosRecomendados)
                ->with('gastos_actuales', $gastosFijosReales)
                ->with('reinversion_recomendada', $reinversionRecomendada)
                ->with('proyeccion_recomendada', $promedioVentas * 1.10)
                ->withInput();
        }

        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $negocio = Negocio::create([
            'usuario_id'       => $usuario->id,
            'nombre_comercial' => $request->nombre_comercial,
            'pais'             => $request->pais,
            'moneda'           => $request->moneda,
            'tipo_negocio'     => $tipoNegocio,
            'direccion'        => $request->direccion,
            'telefono'         => $request->telefono,
        ]);

        $metaInicial = $promedioVentas > $peCalculado
            ? $promedioVentas * 1.10
            : ($peCalculado > 0 ? $peCalculado * 1.10 : $promedioVentas * 1.10);

        ConfigEstrategica::create([
            'negocio_id'                  => $negocio->id,
            'margen_operacional'          => $margenOperacional,
            'dias_operacion'              => $request->dias_operacion,
            'sueldo_dueno'                => $request->sueldo_dueno,
            'ingresos_proyectados'        => $request->ingresos_proyectados ?? $metaInicial,
            'utilidad_ahorro_reinversion' => $request->utilidad_ahorro_reinversion,
            'dinero_disponible'           => $request->dinero_disponible,
            'ventas_mes1'                 => $request->ventas_mes1,
            'ventas_mes2'                 => $request->ventas_mes2,
            'ventas_mes3'                 => $request->ventas_mes3,
            'presupuesto_compras_mensual' => $request->presupuesto_compras_mensual ?? 0,
        ]);

        MetaMensual::create([
            'negocio_id'       => $negocio->id,
            'mes'              => now()->month,
            'anio'             => now()->year,
            'meta'             => $metaInicial,
            'punto_equilibrio' => $peCalculado,
            'ventas_real'      => 0,
            'alerta'           => null,
        ]);

        if ($request->servicios_mes)    GastoFijo::create(['negocio_id' => $negocio->id, 'descripcion' => 'Servicios',         'monto' => $request->servicios_mes,     'activo' => true]);
        if ($request->renta_local)      GastoFijo::create(['negocio_id' => $negocio->id, 'descripcion' => 'Renta del local',   'monto' => $request->renta_local,       'activo' => true]);
        if ($request->nomina_empleados) GastoFijo::create(['negocio_id' => $negocio->id, 'descripcion' => 'Nómina empleados',  'monto' => $request->nomina_empleados,  'activo' => true]);
        if ($request->otros_gastos_fijos) GastoFijo::create(['negocio_id' => $negocio->id, 'descripcion' => 'Otros gastos fijos', 'monto' => $request->otros_gastos_fijos, 'activo' => true]);

        Auth::login($usuario);

        return $negocio->tieneInventario()
            ? redirect()->route('inventario.index')->with('success', '¡Bienvenido! Empieza registrando tu inventario.')
            : redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu negocio está configurado.');
    }

    // =====================================================
    // DASHBOARD
    // =====================================================
    public function showDashboard(DashboardService $dashboardService)
    {
        $negocio = Auth::user()->negocio;
        $config  = $negocio->configEstrategica;

        $datos = $dashboardService->obtenerDatosDashboard($negocio, $config);

        return view('dashboard', $datos);
    }

    // =====================================================
    // EDITAR CONFIGURACIÓN
    // =====================================================
    public function editarConfiguracion()
    {
        $negocio     = Auth::user()->negocio;
        $config      = $negocio->configEstrategica;
        $gastosFijos = $negocio->gastosFijos()->where('activo', 1)->get();
        return view('configuracion.editar', compact('negocio', 'config', 'gastosFijos'));
    }

    public function actualizarConfiguracion(Request $request)
    {
        $negocio = Auth::user()->negocio;
        $config  = $negocio->configEstrategica;

        $request->validate([
            'nombre_comercial'            => 'required',
            'pais'                        => 'required',
            'moneda'                      => 'required',
            'margen_operacional'          => 'nullable|numeric|min:0',
            'dias_operacion'              => 'required|numeric|min:1',
            'sueldo_dueno'                => 'required|numeric|min:0',
            'ingresos_proyectados'        => 'required|numeric|min:0',
            'utilidad_ahorro_reinversion' => 'required|numeric|min:0',
            'dinero_disponible'           => 'required|numeric|min:0',
        ]);

        $gastosFijosTotal = $negocio->gastosFijos()->where('activo', 1)->sum('monto');
        $pe = FinanzasService::puntoEquilibrio($gastosFijosTotal, $request->sueldo_dueno, $request->margen_operacional ?? 0);

        if ($negocio->esServicios() && $request->ingresos_proyectados <= $pe) {
            return back()->withErrors(['ingresos_proyectados' => 'Deben ser mayores al PE (' . number_format($pe, 0, ',', '.') . ')'])->withInput();
        }

       $negocio->update([
        'nombre_comercial' => $request->nombre_comercial,
        'pais'             => $request->pais,
        'moneda'           => $request->moneda,
        'direccion'        => $request->direccion,
        'telefono'         => $request->telefono,
         ]);

        $config->update([
            'margen_operacional'          => $request->margen_operacional ?? 0,
            'dias_operacion'              => $request->dias_operacion,
            'sueldo_dueno'                => $request->sueldo_dueno,
            'ingresos_proyectados'        => $request->ingresos_proyectados,
            'utilidad_ahorro_reinversion' => $request->utilidad_ahorro_reinversion,
            'dinero_disponible'           => $request->dinero_disponible,
        ]);

        $negocio->metasMensuales()->where('mes', now()->month)->where('anio', now()->year)
            ->update(['punto_equilibrio' => $pe]);

        return redirect('/dashboard')->with('success', 'Configuración actualizada.');
    }

    // =====================================================
    // GASTOS FIJOS
    // =====================================================
    public function crearGastoFijo() { return view('gastofijo.crear'); }

    public function guardarGastoFijo(Request $request)
    {
        $request->validate(['descripcion' => 'required', 'monto' => 'required|numeric|min:0']);
        GastoFijo::create(['negocio_id' => Auth::user()->negocio->id, 'descripcion' => $request->descripcion, 'monto' => $request->monto, 'activo' => true]);
        return redirect('/configuracion/editar')->with('success', 'Gasto fijo agregado.');
    }

    public function eliminarGastoFijo($id)
    {
        GastoFijo::findOrFail($id)->delete();
        return redirect('/configuracion/editar')->with('success', 'Gasto fijo eliminado.');
    }

    public function editarGastoFijo($id)
    {
        $gastoFijo = GastoFijo::findOrFail($id);
        return view('gastofijo.editar', compact('gastoFijo'));
    }

    public function actualizarGastoFijo(Request $request, $id)
    {
        $request->validate(['descripcion' => 'required', 'monto' => 'required|numeric|min:0']);
        GastoFijo::findOrFail($id)->update(['descripcion' => $request->descripcion, 'monto' => $request->monto]);
        return redirect('/configuracion/editar')->with('success', 'Gasto fijo actualizado.');
    }

    // =====================================================
    // BANNER Y RECOMENDACIONES
    // =====================================================
    public function cerrarBanner(Request $request)
    {
        $mes  = now()->month == 1 ? 12 : now()->month - 1;
        $anio = now()->month == 1 ? now()->year - 1 : now()->year;
        session(['banner_cerrado_' . $mes . '_' . $anio => true]);
        return response()->json(['ok' => true]);
    }

    public function marcarRecomendacionesVistas(Request $request)
    {
        session(['recomendaciones_vistas_' . now()->month . '_' . now()->year => true]);
        return response()->json(['ok' => true]);
    }

    public function verificarEmail(Request $request)
    {
        return response()->json(['existe' => User::where('email', $request->email)->exists()]);
    }
}