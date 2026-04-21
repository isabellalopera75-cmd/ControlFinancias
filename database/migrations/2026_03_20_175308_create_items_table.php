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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['insumo', 'producto', 'servicio']);
            $table->float('costo_compra')->default(0);
            $table->float('precio_venta')->default(0);
            $table->float('stock')->default(0);
            $table->string('unidad', 50)->nullable();
            $table->string('unidad_base', 50)->nullable();
            $table->float('factor_conversion')->default(1);
            $table->float('stock_minimo')->default(0);
            $table->boolean('tiene_stock')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
