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
        Schema::table('orden_venta', function (Blueprint $table) {
            $table->unsignedBigInteger('guia_prestamo_id')->nullable()->after('documento_transporte_id');
            $table->foreign('guia_prestamo_id')
                  ->references('id')
                  ->on('guias_prestamo')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('orden_venta', function (Blueprint $table) {
            $table->dropForeign(['guia_prestamo_id']);
            $table->dropColumn('guia_prestamo_id');
        });
    }
};