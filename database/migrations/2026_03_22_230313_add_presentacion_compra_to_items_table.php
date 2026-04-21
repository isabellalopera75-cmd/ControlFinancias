<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->enum('presentacion_compra', ['unidad', 'caja'])
                ->default('unidad')
                ->after('stock_minimo');
            $table->unsignedInteger('unidades_por_caja')
                ->nullable()
                ->after('presentacion_compra');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['presentacion_compra', 'unidades_por_caja']);
        });
    }
};
