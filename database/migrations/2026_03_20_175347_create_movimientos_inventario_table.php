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
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->id();
                $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
                $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
                $table->float('cantidad');
                $table->float('costo_unitario')->default(0);
                $table->unsignedBigInteger('referencia_id')->nullable();
                $table->foreign('referencia_id')
                    ->references('id')
                    ->on('movimientos_caja')
                    ->nullOnDelete();
                $table->date('fecha');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('movimientos_inventario');
        }
};
