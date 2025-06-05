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
        Schema::table('orden_venta_detalle', function (Blueprint $table) {
            $table->tinyInteger('tipo_promocion')->nullable()->after('cantidad')->comment('1 es escala, 2 es escala grupal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_venta_detalle', function (Blueprint $table) {
            $table->dropColumn(['tipo_promocion']);
        });
    }
};
