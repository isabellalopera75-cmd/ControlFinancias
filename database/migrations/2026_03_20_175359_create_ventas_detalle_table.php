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
        Schema::create('ventas_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_caja_id')->constrained('movimientos_caja')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->float('cantidad');
            $table->float('precio_unitario');
            $table->float('costo_unitario')->default(0);
            $table->float('costo_total')->default(0);
            $table->float('subtotal')->default(0);
            $table->float('markup')->nullable();
            $table->float('margen_real')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_detalle');
    }
};
