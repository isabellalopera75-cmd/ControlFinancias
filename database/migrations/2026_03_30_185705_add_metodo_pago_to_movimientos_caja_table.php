<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->enum('metodo_pago', ['efectivo', 'transferencia'])
                  ->default('efectivo')
                  ->after('es_venta');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};