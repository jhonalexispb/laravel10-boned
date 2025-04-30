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
        Schema::table('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->unsignedBigInteger('type_comprobante_compra_id')
                ->after('orden_compra_id');
            $table->foreign('type_comprobante_compra_id')
                ->references('id')
                ->on('type_comprobante_pago_compra')
                ->onDelete('restrict');
            $table->dateTime('fecha_emision')
                ->after('serie');
            $table->longText('comentario')
                ->nullable()
                ->after('fecha_emision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->dropForeign('ordenes_compra_n_comprobantes_type_comprobante_compra_id_foreign');
            $table->dropColumn('type_comprobante_compra_id');
            $table->dropColumn('fecha_emision');
            $table->dropColumn('comentario');
        });
    }
};
