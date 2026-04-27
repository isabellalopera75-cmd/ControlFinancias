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

class NegocioController extends Controller
{
    // =====================================================
    // CONFIGURACIÓN INICIAL
    // =====================================================
    public function showConfiguracion()
    {
        return view('configuracion-inicial');
    }

    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'name'                        => 'required',
            'email'                       => 'required|email|unique:users',
            'password'                    => 'required|min:6',
            'nombre_comercial'            => 'required',
            'pais'                        => 'required',
            'moneda'                      => 'required',
            'tipo_negocio'                => 'required|in:servicios,reventa',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
            'margen_operacional'          => 'nullable|numeric',
            'dias_operacion'              => 'required|numeric',
            'sueldo_dueno'                => 'required|numeric',
            'ingresos_proyectados'        => 'nullable|numeric|min:0',
            'utilidad_ahorro_reinversion' => 'required|numeric',
            'dinero_disponible'           => 'required|numeric',
            'nomina_empleados'            => 'nullable|numeric|min:0',
            'ventas_mes1'                 => 'required|numeric|min:0',
            'ventas_mes2'                 => 'required|numeric|min:0',
            'ventas_mes3'                 => 'required|numeric|min:0',
        ]);

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
    public function showDashboard()
    {
        $negocio = Auth::user()->negocio;
        $config  = $negocio->configEstrategica;

        $margenPonderado = 0; 
        $utilidadMes = 0;

        $gastosFijos = $negocio->gastosFijos()->where('activo', 1)->sum('monto');

        $ventasMes = $negocio->movimientosCaja()
            ->where('es_venta', true)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        $gastosMes = $negocio->movimientosCaja()
            ->where('es_venta', false)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        // ─────────────────────────────────────────────
        // LÓGICA POR TIPO DE NEGOCIO
        // ─────────────────────────────────────────────
        if ($negocio->esServicios()) {

            $margenPonderado = null;
            $costoVentasMes  = 0;

            $puntoEquilibrio = $gastosFijos + $config->sueldo_dueno;

            $avanceReal  = $ventasMes - $gastosMes;
            $utilidadMes = FinanzasService::utilidadMensual(
                $ventasMes, $gastosFijos, $gastosMes, $config->sueldo_dueno
            );

            $gastoDiario = $config->dias_operacion > 0
                ? ($gastosFijos + $gastosMes) / $config->dias_operacion
                : 0;

        } else {

            // REVENTA
            $margenPonderado = $this->calcularMargenPonderado($negocio, now()->month, now()->year);

            if ($margenPonderado <= 0) {
                $margenPonderado = $this->calcularMargenPromedioInventario($negocio);
            }

            $puntoEquilibrio = $margenPonderado > 0
                ? ($gastosFijos + $config->sueldo_dueno) / $margenPonderado
                : 0;

            $costoVentasMes = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio) {
                $q->where('negocio_id', $negocio->id)
                  ->whereMonth('fecha', now()->month)
                  ->whereYear('fecha', now()->year);
            })->sum('costo_total');

            $avanceReal  = $ventasMes - $costoVentasMes - $gastosMes;
            $utilidadMes = FinanzasService::utilidadMensual(
                $ventasMes, $gastosFijos, $gastosMes, $config->sueldo_dueno, $costoVentasMes
            );

            $gastoDiario = $config->dias_operacion > 0
                ? ($gastosFijos + $costoVentasMes + $gastosMes) / $config->dias_operacion
                : 0;
        }

        // ─────────────────────────────────────────────
        // DÍAS SUPERVIVENCIA
        // ─────────────────────────────────────────────
        $dineroDisponibleReal = $config->dinero_disponible + $utilidadMes;
        $diasSupervivencia    = $gastoDiario > 0 ? floor(max($dineroDisponibleReal, 0) / $gastoDiario) : 999;

        // ─────────────────────────────────────────────
        // META MENSUAL
        // ─────────────────────────────────────────────
        $ultimaMeta = $negocio->metasMensuales()
            ->orderBy('anio', 'desc')->orderBy('mes', 'desc')->first();

        if ($ultimaMeta && ($ultimaMeta->mes != now()->month || $ultimaMeta->anio != now()->year)) {

            $ventasRealesAnterior = $negocio->movimientosCaja()
                ->where('es_venta', true)
                ->whereMonth('fecha', $ultimaMeta->mes)
                ->whereYear('fecha', $ultimaMeta->anio)
                ->sum('monto');

            $ultimaMeta->update(['ventas_real' => $ventasRealesAnterior]);

            $nuevaMeta = $ventasRealesAnterior >= $ultimaMeta->meta
                ? $ultimaMeta->meta * 1.10
                : $ultimaMeta->meta * 0.95;

            if ($nuevaMeta < $puntoEquilibrio) $nuevaMeta = $puntoEquilibrio * 1.10;

            $metaMes = MetaMensual::create([
                'negocio_id'       => $negocio->id,
                'mes'              => now()->month,
                'anio'             => now()->year,
                'meta'             => $nuevaMeta,
                'punto_equilibrio' => $puntoEquilibrio,
                'ventas_real'      => 0,
                'alerta'           => null,
            ]);

        } else {
            $metaMes = $negocio->metasMensuales()
                ->where('mes', now()->month)
                ->where('anio', now()->year)
                ->first();

            if (!$metaMes) {
                $metaBase = $ultimaMeta ? (float)$ultimaMeta->meta : (float)$config->ingresos_proyectados;
                $metaBase = $metaBase > 0 ? $metaBase : 1000000;

                $metaMes = MetaMensual::create([
                    'negocio_id'       => $negocio->id,
                    'mes'              => now()->month,
                    'anio'             => now()->year,
                    'meta'             => $metaBase,
                    'punto_equilibrio' => $puntoEquilibrio,
                    'ventas_real'      => 0,
                    'alerta'           => null,
                ]);
            }
        }
        // Corregir meta si está por debajo del PE
        if ($metaMes && $metaMes->meta < $puntoEquilibrio && $puntoEquilibrio > 0) {
            $metaMes->update([
                'meta'             => round($puntoEquilibrio * 1.10),
                'punto_equilibrio' => $puntoEquilibrio,
            ]);
            $metaMes->meta = round($puntoEquilibrio * 1.10);
        }

        // Auto-Sanación: Si la meta quedó inflada por un PE erróneo del pasado
        if ($metaMes && $puntoEquilibrio > 0) {
            $promedioHistorico = ($config->ventas_mes1 + $config->ventas_mes2 + $config->ventas_mes3) / 3;
            if ($promedioHistorico <= 0) $promedioHistorico = $puntoEquilibrio;
            
            $techoLogico = max($puntoEquilibrio * 1.70, $promedioHistorico * 1.70);
            
            if ($metaMes->meta > $techoLogico && $metaMes->meta > 1000) {
                $metaOptima = max($puntoEquilibrio * 1.10, $promedioHistorico * 1.10);
                $metaMes->update(['meta' => round($metaOptima)]);
                $metaMes->meta = round($metaOptima);
            }
        }

        // ─────────────────────────────────────────────
        // PORCENTAJE Y PROYECCIÓN
        // ─────────────────────────────────────────────
        $porcentajeAvance = $metaMes->meta > 0
            ? min(max(($avanceReal / $metaMes->meta) * 100, 0), 100)
            : 0;

        $diasTranscurridos = now()->day;
        $diasDelMes        = now()->daysInMonth;
        $diasRestantes     = $diasDelMes - $diasTranscurridos;

        $totalVentas = $negocio->movimientosCaja()
            ->where('es_venta', true)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $proyeccionCierre = $totalVentas >= 4 && $diasTranscurridos > 0
            ? ($ventasMes / $diasTranscurridos) * $diasDelMes
            : null;

        // ─────────────────────────────────────────────
        // BANNER MES ANTERIOR
        // ─────────────────────────────────────────────
        $mostrarBanner = false;
        $bannerData    = null;

        if (now()->day <= 3) {
            $mesAnterior  = now()->month == 1 ? 12 : now()->month - 1;
            $anioAnterior = now()->month == 1 ? now()->year - 1 : now()->year;
            $metaAnterior = $negocio->metasMensuales()
                ->where('mes', $mesAnterior)->where('anio', $anioAnterior)->first();

            if ($metaAnterior && !session('banner_cerrado_' . $mesAnterior . '_' . $anioAnterior)) {
                $mostrarBanner = true;
                $bannerData    = $metaAnterior;
            }
        }

        // ─────────────────────────────────────────────
        // ALERTA
        // ─────────────────────────────────────────────
        $porcentajeMesTranscurrido = ($diasTranscurridos / $diasDelMes) * 100;
        $faltante                  = $metaMes->meta - $avanceReal;
        $ventaDiariaRequerida      = $diasRestantes > 0 ? $faltante / $diasRestantes : 0;

        $alerta = null;
        if ($porcentajeMesTranscurrido >= 80 && $avanceReal < $puntoEquilibrio) {
            $alerta = ['tipo' => 'rojo', 'mensaje' => 'Riesgo crítico: necesitas generar ' . $negocio->moneda . ' ' . number_format($ventaDiariaRequerida, 0, ',', '.') . ' por día.'];
        } elseif ($porcentajeMesTranscurrido >= 50 && $porcentajeAvance < 50) {
            $alerta = ['tipo' => 'amarillo', 'mensaje' => 'Vas retrasado. Necesitas generar ' . $negocio->moneda . ' ' . number_format($ventaDiariaRequerida, 0, ',', '.') . ' por día.'];
        }

        // ─────────────────────────────────────────────
        // MOVIMIENTOS
        // ─────────────────────────────────────────────
        $movimientos = $negocio->movimientosCaja()
            ->withCount(['ventasDetalle', 'comprasDetalle'])
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // ─────────────────────────────────────────────
        // RECOMENDACIONES
        // ─────────────────────────────────────────────
        $diaActual       = now()->day;
        $recomendaciones = [];

        $tipoEstado = $avanceReal < $puntoEquilibrio ? 'riesgo'
            : ($avanceReal < $metaMes->meta ? 'estable' : 'prospero');

        if ($ventasMes == 0 && $gastosMes == 0) {
            $recomendaciones[] = ['tipo' => 'info', 'mensaje' => 'Registra tus ventas y gastos para recibir recomendaciones.', 'accion' => null];
        } else {
            $ventaDiariaReq = $diasRestantes > 0 ? ($metaMes->meta - $avanceReal) / $diasRestantes : 0;

            if ($diaActual <= 7) {
                $mesAnteriorNum  = now()->month == 1 ? 12 : now()->month - 1;
                $anioAnteriorNum = now()->month == 1 ? now()->year - 1 : now()->year;
                $resumenAnterior = $negocio->metasMensuales()
                    ->where('mes', $mesAnteriorNum)->where('anio', $anioAnteriorNum)->first();

                if ($resumenAnterior) {
                    $supero = $resumenAnterior->ventas_real >= $resumenAnterior->meta;
                    $recomendaciones[] = ['tipo' => $supero ? 'verde' : 'rojo',
                        'mensaje' => 'Mes anterior: ' . ($supero ? '¡Superaste tu proyección!' : 'No alcanzaste la proyección.'), 'accion' => null];
                }
                $recomendaciones[] = ['tipo' => 'info', 'mensaje' => 'Inicio de mes: mantén un buen ritmo desde el primer día.', 'accion' => null];

            } elseif ($diaActual <= 20) {
                if ($tipoEstado === 'prospero') {
                    $recomendaciones[] = ['tipo' => 'verde', 'mensaje' => '¡Vas muy bien! Mantén el ritmo.', 'accion' => null];
                } elseif ($tipoEstado === 'estable') {
                    $recomendaciones[] = ['tipo' => 'amarillo', 'mensaje' => 'Necesitas generar ' . $negocio->moneda . ' ' . number_format($ventaDiariaReq, 0, ',', '.') . ' por día.', 'accion' => null];
                } else {
                    $recomendaciones[] = ['tipo' => 'rojo', 'mensaje' => 'Estás en riesgo. No cubres el punto de equilibrio.', 'accion' => null];
                }
            } else {
                if ($tipoEstado === 'prospero') {
                    $recomendaciones[] = ['tipo' => 'verde', 'mensaje' => '¡Excelente! Cerrarás el mes superando tu proyección.', 'accion' => null];
                } else {
                    $recomendaciones[] = ['tipo' => 'rojo', 'mensaje' => 'Quedan pocos días. Necesitas ' . $negocio->moneda . ' ' . number_format($ventaDiariaReq, 0, ',', '.') . ' por día.', 'accion' => null];
                }
            }

            if ($tipoEstado === 'riesgo') {
                $recomendaciones[] = ['tipo' => 'accion', 'mensaje' => 'Revisa tus gastos fijos: ' . $negocio->moneda . ' ' . number_format($gastosFijos, 0, ',', '.') . ' mensuales.',
                    'accion' => ['texto' => 'Reducir gastos', 'url' => '/configuracion/editar#gastos']];
            } elseif ($tipoEstado === 'prospero') {
                $recomendaciones[] = ['tipo' => 'accion', 'mensaje' => 'Las cosas van bien. Considera aumentar tu sueldo.',
                    'accion' => ['texto' => 'Ajustar sueldo', 'url' => '/configuracion/editar#sueldo']];
            }
        }

        $hayRecomendacionesNuevas = !session('recomendaciones_vistas_' . now()->month . '_' . now()->year);

        // ─────────────────────────────────────────────
        // INVENTARIO (solo reventa)
        // ─────────────────────────────────────────────
        $stockBajo = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('activo', true)
                  ->where('tiene_stock', true)->whereColumn('stock', '<=', 'stock_minimo')->get()
            : collect();

        $productosVendibles = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('tipo', 'producto')
                  ->where('activo', true)->get(['id', 'nombre', 'precio_venta', 'costo_compra', 'stock'])
            : collect();

        $itemsConStock = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('tiene_stock', true)
                  ->where('activo', true)->get(['id', 'nombre', 'stock', 'unidad_base'])
            : collect();

        return view('dashboard', compact(
            'negocio', 'config', 'gastosFijos',
            'puntoEquilibrio', 'margenPonderado',
            'ventasMes', 'gastosMes', 'utilidadMes',
            'porcentajeAvance', 'avanceReal',
            'diasSupervivencia', 'movimientos',
            'metaMes', 'alerta',
            'mostrarBanner', 'bannerData',
            'proyeccionCierre',
            'recomendaciones', 'tipoEstado',
            'hayRecomendacionesNuevas',
            'stockBajo', 'productosVendibles', 'itemsConStock'
        ));
    }

    // ─────────────────────────────────────────────
    // MÉTODOS PRIVADOS
    // ─────────────────────────────────────────────
    private function calcularMargenPonderado($negocio, $mes, $anio): float
    {
        $ventas = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio, $mes, $anio) {
            $q->where('negocio_id', $negocio->id)
              ->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
        })->get();

        $totalVentas = $ventas->sum('subtotal');
        if ($totalVentas <= 0) return 0;

        return $ventas->sum(fn($v) => (($v->margen_real ?? 0) / 100) * $v->subtotal) / $totalVentas;
    }

    private function calcularMargenPromedioInventario($negocio): float
    {
        $items = Item::where('negocio_id', $negocio->id)
            ->where('tipo', 'producto')->where('activo', true)->get();

        if ($items->isEmpty()) return 0;

        $totalMargen = 0;
        $count = 0;

        foreach ($items as $item) {
            if ($item->precio_venta <= 0) continue;
            $totalMargen += ($item->precio_venta - $item->costo_compra) / $item->precio_venta;
            $count++;
        }

        return $count > 0 ? $totalMargen / $count : 0;
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
        return view('gastofijo.editar', compact('gastoFijo'))->with('gastoFijo', GastoFijo::findOrFail($id));
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