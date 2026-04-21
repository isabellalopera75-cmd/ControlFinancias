<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->index(['negocio_id', 'fecha'], 'idx_movimientos_negocio_fecha');
            $table->index('es_venta', 'idx_movimientos_es_venta');
        });

        Schema::table('metas_mensuales', function (Blueprint $table) {
            $table->index(['negocio_id', 'mes', 'anio'], 'idx_metas_negocio_mes');
        });

        Schema::table('gastos_fijos', function (Blueprint $table) {
            $table->index(['negocio_id', 'activo'], 'idx_gastos_negocio_activo');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropIndex('idx_movimientos_negocio_fecha');
            $table->dropIndex('idx_movimientos_es_venta');
        });

        Schema::table('metas_mensuales', function (Blueprint $table) {
            $table->dropIndex('idx_metas_negocio_mes');
        });

        Schema::table('gastos_fijos', function (Blueprint $table) {
            $table->dropIndex('idx_gastos_negocio_activo');
        });
    }
};