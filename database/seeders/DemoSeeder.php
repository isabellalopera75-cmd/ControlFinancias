<?php

namespace Database\Seeders;

use App\Models\CompraDetalle;
use App\Models\ConfigEstrategica;
use App\Models\GastoFijo;
use App\Models\Item;
use App\Models\MetaMensual;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Models\User;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data for a Colombian minimarket (MiniMarket El Vecino).
     *
     * This seeder is idempotent: it deletes any existing demo data before inserting.
     * All monetary values are in COP. All text is in Spanish.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ─── 1. CLEANUP ──────────────────────────────────────────────
            $existingUser = User::where('email', 'demo@impulweb.test')->first();
            if ($existingUser) {
                $negocioIds = Negocio::where('usuario_id', $existingUser->id)->pluck('id');
                if ($negocioIds->isNotEmpty()) {
                    // Eliminar registros dependientes manualmente (sin depender de CASCADE)
                    $movCajaIds = MovimientoCaja::whereIn('negocio_id', $negocioIds)->pluck('id');
                    VentaDetalle::whereIn('movimiento_caja_id', $movCajaIds)->delete();
                    CompraDetalle::whereIn('movimiento_caja_id', $movCajaIds)->delete();
                    MovimientoInventario::whereIn('negocio_id', $negocioIds)->delete();
                    MovimientoCaja::whereIn('negocio_id', $negocioIds)->delete();
                    Item::whereIn('negocio_id', $negocioIds)->delete();
                    MetaMensual::whereIn('negocio_id', $negocioIds)->delete();
                    GastoFijo::whereIn('negocio_id', $negocioIds)->delete();
                    ConfigEstrategica::whereIn('negocio_id', $negocioIds)->delete();
                    Negocio::whereIn('id', $negocioIds)->delete();
                }
                // Limpiar sesiones del usuario demo
                DB::table('sessions')->where('user_id', $existingUser->id)->delete();
                $existingUser->delete();
            }

            // ─── 2. USER ────────────────────────────────────────────────
            $user = User::create([
                'name'     => 'Usuario Demo',
                'email'    => 'demo@impulweb.test',
                'password' => Hash::make('demo1234'),
            ]);

            // ─── 3. NEGOCIO ─────────────────────────────────────────────
            $negocio = Negocio::create([
                'usuario_id'       => $user->id,
                'nombre_comercial' => 'MiniMarket El Vecino',
                'pais'             => 'Colombia',
                'moneda'           => 'COP',
                'tipo_negocio'     => 'reventa',
                'direccion'        => 'Cra 12 #14-35, Barrio Centro, Florencia, Caquetá',
                'telefono'         => '3124567890',
            ]);

            $negocioId = $negocio->id;

            // ─── 4. CONFIG ESTRATÉGICA ──────────────────────────────────
            ConfigEstrategica::create([
                'negocio_id'                 => $negocioId,
                'margen_operacional'         => 28,       // 28% margen promedio
                'dias_operacion'             => 26,
                'sueldo_dueno'               => 1800000,  // $1.800.000 COP
                'ingresos_proyectados'       => 8500000,  // $8.500.000 COP/mes
                'utilidad_ahorro_reinversion' => 500000,
                'dinero_disponible'          => 2350000,
                'ventas_mes1'                => 7820000,  // Mes actual parcial
                'ventas_mes2'                => 8150000,  // Mes anterior
                'ventas_mes3'                => 7640000,  // Hace 2 meses
                'presupuesto_compras_mensual' => 5500000,
            ]);

            // ─── 5. GASTOS FIJOS ────────────────────────────────────────
            $gastosFijos = [
                ['descripcion' => 'Arriendo local',            'monto' => 850000,  'activo' => true],
                ['descripcion' => 'Servicios públicos (agua, luz, gas)', 'monto' => 320000,  'activo' => true],
                ['descripcion' => 'Nómina auxiliar',           'monto' => 1300000, 'activo' => true],
                ['descripcion' => 'Internet y telefonía',      'monto' => 95000,   'activo' => true],
            ];

            foreach ($gastosFijos as $gf) {
                GastoFijo::create(array_merge($gf, ['negocio_id' => $negocioId]));
            }

            // ─── 6. PRODUCTOS (25 items) ────────────────────────────────
            // Each item has: nombre, categoria, tipo, costo_compra (weighted avg),
            // precio_venta, initial_stock (used for seed logic), stock_minimo, etc.
            // "initial_stock" is NOT a DB column — we track it here for calculations.
            $productosData = [
                // ── Bebidas ──
                ['nombre' => 'Gaseosa Postobón 350ml',    'categoria' => 'Bebidas',  'costo_compra' => 1200,  'precio_venta' => 2000,  'initial_stock' => 48, 'stock_minimo' => 12, 'unidad' => 'unidad'],
                ['nombre' => 'Agua Cristal 600ml',        'categoria' => 'Bebidas',  'costo_compra' => 800,   'precio_venta' => 1500,  'initial_stock' => 36, 'stock_minimo' => 10, 'unidad' => 'unidad'],
                ['nombre' => 'Jugo Hit 250ml',             'categoria' => 'Bebidas',  'costo_compra' => 950,   'precio_venta' => 1800,  'initial_stock' => 30, 'stock_minimo' => 8,  'unidad' => 'unidad'],
                ['nombre' => 'Cerveza Águila 330ml',       'categoria' => 'Bebidas',  'costo_compra' => 2100,  'precio_venta' => 3500,  'initial_stock' => 24, 'stock_minimo' => 6,  'unidad' => 'unidad'],
                ['nombre' => 'Pony Malta 330ml',           'categoria' => 'Bebidas',  'costo_compra' => 1400,  'precio_venta' => 2500,  'initial_stock' => 24, 'stock_minimo' => 6,  'unidad' => 'unidad'],
                // ── Granos ──
                ['nombre' => 'Arroz Roa x 500g',          'categoria' => 'Granos',   'costo_compra' => 2200,  'precio_venta' => 3200,  'initial_stock' => 20, 'stock_minimo' => 5,  'unidad' => 'unidad'],
                ['nombre' => 'Frijol Rojo x 500g',        'categoria' => 'Granos',   'costo_compra' => 3500,  'precio_venta' => 5000,  'initial_stock' => 15, 'stock_minimo' => 4,  'unidad' => 'unidad'],
                ['nombre' => 'Lenteja x 500g',            'categoria' => 'Granos',   'costo_compra' => 3200,  'precio_venta' => 4500,  'initial_stock' => 12, 'stock_minimo' => 3,  'unidad' => 'unidad'],
                ['nombre' => 'Azúcar Manuelita x 1kg',    'categoria' => 'Granos',   'costo_compra' => 3000,  'precio_venta' => 4200,  'initial_stock' => 18, 'stock_minimo' => 5,  'unidad' => 'unidad'],
                ['nombre' => 'Aceite Girasol x 1L',       'categoria' => 'Granos',   'costo_compra' => 7500,  'precio_venta' => 10500, 'initial_stock' => 10, 'stock_minimo' => 3,  'unidad' => 'unidad'],
                // ── Lácteos ──
                ['nombre' => 'Leche Alquería entera 1L',  'categoria' => 'Lácteos',  'costo_compra' => 3800,  'precio_venta' => 5200,  'initial_stock' => 20, 'stock_minimo' => 5,  'unidad' => 'unidad'],
                ['nombre' => 'Queso campesino x 250g',    'categoria' => 'Lácteos',  'costo_compra' => 4500,  'precio_venta' => 6500,  'initial_stock' => 8,  'stock_minimo' => 2,  'unidad' => 'unidad'],
                ['nombre' => 'Yogurt Alpina 150g',        'categoria' => 'Lácteos',  'costo_compra' => 1800,  'precio_venta' => 2800,  'initial_stock' => 15, 'stock_minimo' => 4,  'unidad' => 'unidad'],
                ['nombre' => 'Mantequilla x 125g',        'categoria' => 'Lácteos',  'costo_compra' => 3200,  'precio_venta' => 4800,  'initial_stock' => 8,  'stock_minimo' => 2,  'unidad' => 'unidad'],
                ['nombre' => 'Huevos AA x 30 und',        'categoria' => 'Lácteos',  'costo_compra' => 14000, 'precio_venta' => 18500, 'initial_stock' => 6,  'stock_minimo' => 2,  'unidad' => 'unidad'],
                // ── Aseo ──
                ['nombre' => 'Jabón Rey x 300g',          'categoria' => 'Aseo',     'costo_compra' => 2800,  'precio_venta' => 4000,  'initial_stock' => 15, 'stock_minimo' => 4,  'unidad' => 'unidad'],
                ['nombre' => 'Detergente Fab x 500g',     'categoria' => 'Aseo',     'costo_compra' => 4200,  'precio_venta' => 6000,  'initial_stock' => 10, 'stock_minimo' => 3,  'unidad' => 'unidad'],
                ['nombre' => 'Papel higiénico x 4 rollos','categoria' => 'Aseo',     'costo_compra' => 5500,  'precio_venta' => 7800,  'initial_stock' => 12, 'stock_minimo' => 3,  'unidad' => 'paquete'],
                ['nombre' => 'Cloro x 500ml',             'categoria' => 'Aseo',     'costo_compra' => 2200,  'precio_venta' => 3500,  'initial_stock' => 10, 'stock_minimo' => 3,  'unidad' => 'unidad'],
                ['nombre' => 'Crema dental Colgate 75ml', 'categoria' => 'Aseo',     'costo_compra' => 3800,  'precio_venta' => 5500,  'initial_stock' => 10, 'stock_minimo' => 3,  'unidad' => 'unidad'],
                // ── Snacks ──
                ['nombre' => 'Papas Margarita 30g',        'categoria' => 'Snacks',   'costo_compra' => 900,   'precio_venta' => 1500,  'initial_stock' => 40, 'stock_minimo' => 10, 'unidad' => 'unidad'],
                ['nombre' => 'Chocoramo',                  'categoria' => 'Snacks',   'costo_compra' => 1100,  'precio_venta' => 1800,  'initial_stock' => 30, 'stock_minimo' => 8,  'unidad' => 'unidad'],
                ['nombre' => 'Galletas Festival',          'categoria' => 'Snacks',   'costo_compra' => 800,   'precio_venta' => 1300,  'initial_stock' => 35, 'stock_minimo' => 10, 'unidad' => 'unidad'],
                ['nombre' => 'De Todito 45g',              'categoria' => 'Snacks',   'costo_compra' => 1400,  'precio_venta' => 2500,  'initial_stock' => 25, 'stock_minimo' => 6,  'unidad' => 'unidad'],
                ['nombre' => 'Bon Bon Bum',                'categoria' => 'Snacks',   'costo_compra' => 350,   'precio_venta' => 700,   'initial_stock' => 50, 'stock_minimo' => 15, 'unidad' => 'unidad'],
            ];

            // Create items in DB and store references with their index for later use
            $items = [];
            foreach ($productosData as $idx => $p) {
                $items[$idx] = Item::create([
                    'negocio_id'          => $negocioId,
                    'nombre'              => $p['nombre'],
                    'categoria'           => $p['categoria'],
                    'tipo'                => 'producto',
                    'costo_compra'        => $p['costo_compra'],
                    'precio_venta'        => $p['precio_venta'],
                    'stock'               => $p['initial_stock'], // Will be adjusted after movements
                    'unidad'              => $p['unidad'],
                    'unidad_base'         => $p['unidad'],
                    'factor_conversion'   => 1,
                    'stock_minimo'        => $p['stock_minimo'],
                    'tiene_stock'         => true,
                    'activo'              => true,
                    'presentacion_compra' => 'unidad',
                    'unidades_por_caja'   => null,
                ]);
            }

            // ─── 7. INITIAL STOCK (Inventory Entries) ───────────────────
            // Record the initial stock as "entrada" movements, dated 46 days ago
            $initialDate = Carbon::now()->subDays(46)->startOfDay();

            foreach ($productosData as $idx => $p) {
                MovimientoInventario::create([
                    'negocio_id'     => $negocioId,
                    'item_id'        => $items[$idx]->id,
                    'tipo'           => 'entrada',
                    'cantidad'       => $p['initial_stock'],
                    'costo_unitario' => $p['costo_compra'],
                    'referencia_id'  => null,
                    'fecha'          => $initialDate->toDateString(),
                ]);
            }

            // ─── 8. SALES & EXPENSE MOVEMENTS ──────────────────────────
            // We'll track cumulative sold quantities per item to keep stock consistent
            $soldQty     = array_fill(0, count($productosData), 0);
            $purchasedQty = array_fill(0, count($productosData), 0);

            // Helpers
            $now       = Carbon::now();
            $metodoPago = function () {
                return rand(1, 10) <= 7 ? 'efectivo' : 'transferencia';
            };

            // ── Define all sale transactions ────────────────────────────
            // Each sale: [daysAgo, [ [itemIndex, qty], ... ], description]
            $salesData = [
                [44, [[0, 3], [20, 2], [24, 5]],            'Venta mostrador mañana'],
                [43, [[5, 2], [10, 1]],                      'Venta granos y leche'],
                [42, [[1, 4], [2, 2], [21, 3]],             'Venta bebidas y snacks'],
                [40, [[6, 1], [8, 2], [15, 2]],             'Venta granos y aseo'],
                [39, [[0, 2], [3, 3], [22, 4]],             'Venta variada tarde'],
                [37, [[10, 2], [12, 3], [14, 1]],           'Venta lácteos'],
                [36, [[4, 2], [20, 3], [23, 2]],            'Venta bebidas y snacks'],
                [34, [[9, 1], [7, 1], [11, 1]],             'Venta aceite y queso'],
                [33, [[0, 4], [1, 3], [24, 8]],             'Venta grande mañana'],
                [31, [[16, 1], [17, 2], [19, 1]],           'Venta productos aseo'],
                [30, [[5, 3], [8, 1], [13, 1]],             'Venta granos y mantequilla'],
                [28, [[3, 2], [2, 3], [21, 2]],             'Venta bebidas variadas'],
                [27, [[10, 2], [14, 1], [12, 2]],           'Venta lácteos surtido'],
                [25, [[0, 5], [20, 4], [22, 3]],            'Venta fuerte fin de semana'],
                [24, [[15, 1], [18, 2], [6, 2]],            'Venta aseo y frijol'],
                [22, [[1, 2], [4, 3], [23, 3]],             'Venta bebidas y todito'],
                [21, [[5, 2], [10, 3], [11, 1]],            'Venta arroz, leche y queso'],
                [19, [[0, 3], [3, 2], [24, 6]],             'Venta mostrador tarde'],
                [18, [[7, 2], [9, 1], [16, 2]],             'Venta lenteja, aceite, aseo'],
                [16, [[21, 4], [22, 5], [20, 3]],           'Venta snacks día de pago'],
                [15, [[10, 2], [12, 3], [13, 1]],           'Venta lácteos mañana'],
                [13, [[0, 4], [1, 3], [2, 2]],              'Venta bebidas combo'],
                [12, [[5, 2], [8, 2], [14, 1]],             'Venta granos y huevos'],
                [10, [[3, 3], [4, 2], [19, 2]],             'Venta cervezas y aseo'],
                [9,  [[6, 1], [15, 2], [17, 1]],            'Venta frijol, jabón, detergente'],
                [7,  [[0, 3], [20, 5], [24, 4]],            'Venta rápida mañana'],
                [6,  [[10, 2], [11, 1], [21, 3]],           'Venta leche, queso, chocoramo'],
                [5,  [[22, 3], [23, 2], [1, 2]],            'Venta snacks y agua'],
                [4,  [[5, 1], [9, 1], [8, 2]],              'Venta granos variados'],
                [3,  [[0, 2], [12, 2], [18, 1]],            'Venta gaseosa, yogurt, cloro'],
                [2,  [[14, 1], [10, 1], [3, 2]],            'Venta huevos, leche, cerveza'],
                [1,  [[20, 3], [21, 2], [24, 3]],           'Venta snacks día'],
            ];

            // ── Define purchase transactions (restocking) ───────────────
            // [daysAgo, [ [itemIndex, qty, costo_unitario], ... ], description]
            $purchasesData = [
                [35, [[0, 24, 1200], [1, 12, 800],  [20, 20, 900]],  'Compra bebidas y papas — Dist. La Estrella'],
                [20, [[10, 12, 3800], [5, 10, 2200], [21, 15, 1100]], 'Compra leche, arroz, chocoramo — Proveedor Central'],
                [8,  [[3, 12, 2100], [24, 20, 350],  [14, 4, 14000]], 'Compra cerveza, dulces, huevos — Dist. Amazonía'],
            ];

            // ── Define expense movements (non-sale cash outflows) ───────
            $expensesData = [
                [41, 850000,  'Pago arriendo local mes julio'],
                [38, 320000,  'Pago servicios públicos julio'],
                [35, 1300000, 'Pago nómina auxiliar julio'],
                [32, 95000,   'Pago internet y telefonía julio'],
                [30, 45000,   'Compra bolsas plásticas y servilletas'],
                [20, 65000,   'Reparación vitrina refrigerada'],
                [11, 850000,  'Pago arriendo local mes agosto'],
                [9,  320000,  'Pago servicios públicos agosto'],
                [5,  1300000, 'Pago nómina auxiliar agosto'],
                [3,  95000,   'Pago internet y telefonía agosto'],
            ];

            // ── Process SALES ───────────────────────────────────────────
            foreach ($salesData as $sale) {
                [$daysAgo, $lineItems, $desc] = $sale;
                $fecha = Carbon::now()->subDays($daysAgo)->setTime(rand(7, 19), rand(0, 59), 0);

                // Calculate total sale amount
                $totalVenta = 0;
                foreach ($lineItems as [$itemIdx, $qty]) {
                    $totalVenta += $productosData[$itemIdx]['precio_venta'] * $qty;
                }

                // Create MovimientoCaja (sale)
                $movCaja = MovimientoCaja::create([
                    'negocio_id'              => $negocioId,
                    'monto'                   => $totalVenta,
                    'descripcion'             => $desc,
                    'es_venta'                => true,
                    'metodo_pago'             => $metodoPago(),
                    'fecha'                   => $fecha,
                    'movimiento_inventario_id' => null,
                ]);

                // Create VentaDetalle + MovimientoInventario (salida) for each line
                foreach ($lineItems as [$itemIdx, $qty]) {
                    $item        = $items[$itemIdx];
                    $costoUnit   = $productosData[$itemIdx]['costo_compra'];
                    $precioUnit  = $productosData[$itemIdx]['precio_venta'];
                    $subtotal    = $precioUnit * $qty;
                    $costoTotal  = $costoUnit * $qty;
                    $markup      = $costoUnit > 0 ? round((($precioUnit - $costoUnit) / $costoUnit) * 100, 2) : 0;
                    $margenReal  = $precioUnit > 0 ? round((($precioUnit - $costoUnit) / $precioUnit) * 100, 2) : 0;

                    VentaDetalle::create([
                        'movimiento_caja_id' => $movCaja->id,
                        'item_id'            => $item->id,
                        'cantidad'           => $qty,
                        'precio_unitario'    => $precioUnit,
                        'costo_unitario'     => $costoUnit,
                        'costo_total'        => $costoTotal,
                        'subtotal'           => $subtotal,
                        'markup'             => $markup,
                        'margen_real'        => $margenReal,
                    ]);

                    // Inventory exit
                    MovimientoInventario::create([
                        'negocio_id'     => $negocioId,
                        'item_id'        => $item->id,
                        'tipo'           => 'salida',
                        'cantidad'       => $qty,
                        'costo_unitario' => $costoUnit,
                        'referencia_id'  => $movCaja->id,
                        'fecha'          => $fecha->toDateString(),
                    ]);

                    $soldQty[$itemIdx] += $qty;
                }
            }

            // ── Process PURCHASES ───────────────────────────────────────
            foreach ($purchasesData as $purchase) {
                [$daysAgo, $lineItems, $desc] = $purchase;
                $fecha = Carbon::now()->subDays($daysAgo)->setTime(rand(6, 10), rand(0, 59), 0);

                // Total purchase cost
                $totalCompra = 0;
                foreach ($lineItems as [$itemIdx, $qty, $costoUnit]) {
                    $totalCompra += $costoUnit * $qty;
                }

                // Create MovimientoCaja (purchase — es_venta = false, monto positivo)
                $movCaja = MovimientoCaja::create([
                    'negocio_id'              => $negocioId,
                    'monto'                   => $totalCompra,
                    'descripcion'             => $desc,
                    'es_venta'                => false,
                    'metodo_pago'             => 'transferencia',
                    'fecha'                   => $fecha,
                    'movimiento_inventario_id' => null,
                ]);

                foreach ($lineItems as [$itemIdx, $qty, $costoUnit]) {
                    $item = $items[$itemIdx];

                    CompraDetalle::create([
                        'movimiento_caja_id' => $movCaja->id,
                        'item_id'            => $item->id,
                        'cantidad'           => $qty,
                        'costo_unitario'     => $costoUnit,
                    ]);

                    // Inventory entry
                    MovimientoInventario::create([
                        'negocio_id'     => $negocioId,
                        'item_id'        => $item->id,
                        'tipo'           => 'entrada',
                        'cantidad'       => $qty,
                        'costo_unitario' => $costoUnit,
                        'referencia_id'  => $movCaja->id,
                        'fecha'          => $fecha->toDateString(),
                    ]);

                    $purchasedQty[$itemIdx] += $qty;
                }
            }

            // ── Process EXPENSES (monto positivo, es_venta = false) ─────
            foreach ($expensesData as [$daysAgo, $monto, $desc]) {
                MovimientoCaja::create([
                    'negocio_id'              => $negocioId,
                    'monto'                   => $monto,
                    'descripcion'             => $desc,
                    'es_venta'                => false,
                    'metodo_pago'             => 'transferencia',
                    'fecha'                   => Carbon::now()->subDays($daysAgo)->setTime(rand(8, 12), rand(0, 59), 0),
                    'movimiento_inventario_id' => null,
                ]);
            }

            // ─── 9. UPDATE FINAL STOCK LEVELS ───────────────────────────
            // final_stock = initial_stock - sold + purchased
            foreach ($productosData as $idx => $p) {
                $finalStock = $p['initial_stock'] - $soldQty[$idx] + $purchasedQty[$idx];
                $items[$idx]->update(['stock' => $finalStock]);
            }

            // ─── 10. METAS MENSUALES ────────────────────────────────────
            // Current month (August 2026), previous (July 2026), 2 months ago (June 2026)
            $currentMonth = Carbon::now();

            // Calculate real sales per month from the movements we created
            $salesByMonth = [];
            foreach ($salesData as $sale) {
                [$daysAgo, $lineItems] = $sale;
                $saleDate  = Carbon::now()->subDays($daysAgo);
                $monthKey  = $saleDate->format('Y-m');
                $saleTotal = 0;
                foreach ($lineItems as [$itemIdx, $qty]) {
                    $saleTotal += $productosData[$itemIdx]['precio_venta'] * $qty;
                }
                $salesByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) + $saleTotal;
            }

            // Total fixed costs per month
            $totalGastosFijos = 850000 + 320000 + 1300000 + 95000; // = 2,565,000

            $metas = [
                // 2 months ago (June 2026)
                [
                    'mes'              => $currentMonth->copy()->subMonths(2)->month,
                    'anio'             => $currentMonth->copy()->subMonths(2)->year,
                    'meta'             => 8200000,
                    'punto_equilibrio' => $totalGastosFijos, // breakeven = fixed costs
                    'ventas_real'      => $salesByMonth[$currentMonth->copy()->subMonths(2)->format('Y-m')] ?? 7640000,
                    'alerta'           => 'precaución',
                ],
                // Previous month (July 2026)
                [
                    'mes'              => $currentMonth->copy()->subMonth()->month,
                    'anio'             => $currentMonth->copy()->subMonth()->year,
                    'meta'             => 8500000,
                    'punto_equilibrio' => $totalGastosFijos,
                    'ventas_real'      => $salesByMonth[$currentMonth->copy()->subMonth()->format('Y-m')] ?? 8150000,
                    'alerta'           => null,
                ],
                // Current month (August 2026) — in progress
                [
                    'mes'              => $currentMonth->month,
                    'anio'             => $currentMonth->year,
                    'meta'             => 8500000,
                    'punto_equilibrio' => $totalGastosFijos,
                    'ventas_real'      => $salesByMonth[$currentMonth->format('Y-m')] ?? 0,
                    'alerta'           => 'en progreso',
                ],
            ];

            foreach ($metas as $meta) {
                MetaMensual::create(array_merge($meta, ['negocio_id' => $negocioId]));
            }
        });
    }
}
