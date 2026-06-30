<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('movimiento_caja_id')->constrained('movimientos_caja')->cascadeOnDelete();
            $table->string('numero', 10);
            $table->enum('estado', ['creada', 'enviada', 'vista', 'pagada'])->default('creada');
            $table->string('email_comprador')->nullable();
            $table->timestamp('enviada_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
