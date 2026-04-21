<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('config_estrategica', function (Blueprint $table) {
            $table->float('presupuesto_compras_mensual')->default(0)->after('ventas_mes3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_estrategica', function (Blueprint $table) {
            $table->dropColumn('presupuesto_compras_mensual');
        });
    }
};
