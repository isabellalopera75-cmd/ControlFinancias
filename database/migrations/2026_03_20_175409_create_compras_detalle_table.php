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
            Schema::create('compras_detalle', function (Blueprint $table) {
                $table->id();
                $table->foreignId('movimiento_caja_id')->constrained('movimientos_caja')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
                $table->float('cantidad');
                $table->float('costo_unitario');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('compras_detalle');
        }
};
