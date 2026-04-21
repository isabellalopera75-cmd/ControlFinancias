<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$items = \App\Models\Item::where('negocio_id', 1)->get()->map(function($i) {
    return [
        'id' => $i->id,
        'nombre' => $i->nombre,
        'costo' => $i->costo_compra,
        'precio' => $i->precio_venta,
        'margen' => ($i->precio_venta > 0 ? ($i->precio_venta - $i->costo_compra) / $i->precio_venta : 0)
    ];
});
echo json_encode($items);
