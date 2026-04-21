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
        Schema::create('config_estrategica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->onDelete('cascade');
            $table->float('margen_operacional')->default(0);
            $table->float('ingresos_proyectados')->default(0);
            $table->integer('dias_operacion')->default(26);
            $table->float('sueldo_dueno')->default(0);
            $table->float('utilidad_ahorro_reinversion')->default(0);
            $table->float('dinero_disponible')->default(0);
            $table->float('ventas_mes1')->default(0);
            $table->float('ventas_mes2')->default(0);
            $table->float('ventas_mes3')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_estrategica');
    }
};
