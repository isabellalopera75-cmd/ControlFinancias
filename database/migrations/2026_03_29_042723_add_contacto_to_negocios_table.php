<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('moneda');
            $table->string('telefono', 30)->nullable()->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            if (Schema::hasColumn('negocios', 'direccion')) $table->dropColumn('direccion');
            if (Schema::hasColumn('negocios', 'telefono'))  $table->dropColumn('telefono');
        });
    }
};