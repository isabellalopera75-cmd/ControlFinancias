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
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->unsignedBigInteger('movimiento_inventario_id')
                    ->nullable()
                    ->after('fecha');
                $table->foreign('movimiento_inventario_id')
                    ->references('id')
                    ->on('movimientos_inventario')
                    ->nullOnDelete();
            });
        }

        public function down(): void
        {
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->dropForeign(['movimiento_inventario_id']);
                $table->dropColumn('movimiento_inventario_id');
            });
        }
};
