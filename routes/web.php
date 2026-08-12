<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NegocioController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\AgenteIAController;

// =====================================================
// RUTAS PÚBLICAS
// =====================================================
Route::get('/', fn() => view('bienvenida'));

Route::get('/configuracion-inicial',  [NegocioController::class, 'showConfiguracion']);
Route::post('/configuracion-inicial', [NegocioController::class, 'guardarConfiguracion']);

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/verificar-email', [NegocioController::class, 'verificarEmail']);
Route::post('/login-demo', [AuthController::class, 'loginDemo'])->name('login.demo');

// =====================================================
// RUTAS PROTEGIDAS
// =====================================================
Route::middleware(['auth', 'demo.restrict'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [NegocioController::class, 'showDashboard'])->name('dashboard');

    // Movimientos
    Route::post('/venta', [MovimientoController::class, 'registrarVenta'])->name('venta.registrar');
    Route::post('/gasto', [MovimientoController::class, 'registrarGasto'])->name('gasto.registrar');
    Route::get('/movimiento/{id}/editar', [MovimientoController::class, 'editar'])->name('movimiento.editar');
    Route::put('/movimiento/{id}',        [MovimientoController::class, 'actualizar'])->name('movimiento.actualizar');
    Route::delete('/movimiento/{id}',     [MovimientoController::class, 'eliminar'])->name('movimiento.eliminar');

    // Configuración
    Route::get('/configuracion/editar', [NegocioController::class, 'editarConfiguracion'])->name('configuracion.editar');
    Route::put('/configuracion/editar', [NegocioController::class, 'actualizarConfiguracion'])->name('configuracion.actualizar');

    // Gastos fijos
    Route::get('/gasto-fijo/crear',       [NegocioController::class, 'crearGastoFijo'])->name('gastofijo.crear');
    Route::post('/gasto-fijo',            [NegocioController::class, 'guardarGastoFijo'])->name('gastofijo.guardar');
    Route::get('/gasto-fijo/{id}/editar', [NegocioController::class, 'editarGastoFijo'])->name('gastofijo.editar');
    Route::put('/gasto-fijo/{id}',        [NegocioController::class, 'actualizarGastoFijo'])->name('gastofijo.actualizar');
    Route::delete('/gasto-fijo/{id}',     [NegocioController::class, 'eliminarGastoFijo'])->name('gastofijo.eliminar');
    //rutas de la factura
    Route::get('/facturas',          [FacturaController::class, 'historial'])->name('facturas.historial');
    Route::get('/facturas/{id}',     [FacturaController::class, 'show'])->name('facturas.show');
    Route::get('/facturas/{id}/pdf', [FacturaController::class, 'descargarPdf'])->name('facturas.pdf');
    Route::post('/facturas/{id}/enviar', [FacturaController::class, 'enviarCorreo'])->name('facturas.enviar');
    Route::get('/informes',          [InformeController::class, 'index'])->name('informes.index');
    Route::get('/informes/pdf',      [InformeController::class, 'pdf'])->name('informes.pdf');
    Route::get('/informes/excel',    [InformeController::class, 'excel'])->name('informes.excel');
    // Banner y recomendaciones
    Route::post('/banner/cerrar',                 [NegocioController::class, 'cerrarBanner'])->name('banner.cerrar');
    Route::post('/recomendaciones/marcar-vistas', [NegocioController::class, 'marcarRecomendacionesVistas'])->name('recomendaciones.vistas');

    //agente ia 
    Route::post('/agente-ia/analizar', [AgenteIAController::class, 'analizar'])->name('agente.analizar');

    // Compras
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('/inventario/entradas', [InventarioController::class, 'entradas'])->name('inventario.entradas');
    // Inventario
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/',           [InventarioController::class, 'index'])->name('index');
        Route::get('/crear',      [InventarioController::class, 'create'])->name('create');
        Route::post('/',          [InventarioController::class, 'store'])->name('store');
        Route::get('/{item}/editar',    [InventarioController::class, 'edit'])->name('edit');
        Route::put('/{item}',           [InventarioController::class, 'update'])->name('update');
        Route::delete('/{item}',        [InventarioController::class, 'destroy'])->name('destroy');
        Route::get('/{item}/kardex',    [InventarioController::class, 'kardex'])->name('kardex');
        Route::post('/{item}/ajuste',   [InventarioController::class, 'ajuste'])->name('ajuste');
        Route::post('/{item}/reconstruir', [InventarioController::class, 'reconstruirStock'])->name('reconstruir');
        Route::get('/exportar-plantilla', [InventarioController::class, 'exportarPlantilla'])->name('exportar');
        Route::post('/importar',          [InventarioController::class, 'importar'])->name('importar');
        
    });
});