<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para items (tabla más consultada por negocio_id + activo)
        Schema::table('items', function (Blueprint $table) {
            $table->index(['negocio_id', 'activo'], 'idx_items_negocio_activo');
            $table->index(['negocio_id', 'categoria'], 'idx_items_negocio_categoria');
        });

        // Índice compuesto optimizado para movimientos_caja
        // El patrón más común es: WHERE negocio_id = ? AND es_venta = ? AND fecha BETWEEN ? AND ?
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->index(['negocio_id', 'es_venta', 'fecha'], 'idx_movcaja_negocio_esventa_fecha');
        });

        // Índices para movimientos_inventario
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index(['item_id', 'fecha'], 'idx_movinv_item_fecha');
            $table->index('referencia_id', 'idx_movinv_referencia');
        });

        // Índices para ventas_detalle
        Schema::table('ventas_detalle', function (Blueprint $table) {
            $table->index('movimiento_caja_id', 'idx_ventasdet_movimiento');
            $table->index('item_id', 'idx_ventasdet_item');
        });

        // Índices para compras_detalle
        Schema::table('compras_detalle', function (Blueprint $table) {
            $table->index('movimiento_caja_id', 'idx_comprasdet_movimiento');
        });

        // Índice para facturas
        Schema::table('facturas', function (Blueprint $table) {
            $table->index('movimiento_caja_id', 'idx_facturas_movimiento');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_negocio_activo');
            $table->dropIndex('idx_items_negocio_categoria');
        });

        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropIndex('idx_movcaja_negocio_esventa_fecha');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('idx_movinv_item_fecha');
            $table->dropIndex('idx_movinv_referencia');
        });

        Schema::table('ventas_detalle', function (Blueprint $table) {
            $table->dropIndex('idx_ventasdet_movimiento');
            $table->dropIndex('idx_ventasdet_item');
        });

        Schema::table('compras_detalle', function (Blueprint $table) {
            $table->dropIndex('idx_comprasdet_movimiento');
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropIndex('idx_facturas_movimiento');
        });
    }
};
