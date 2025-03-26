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
        Schema::table('relacion_bank_comprobante', function (Blueprint $table) {
            $table->unique(['id_banco', 'id_comprobante_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relacion_bank_comprobante', function (Blueprint $table) {
            // Elimina las claves foráneas antes de eliminar la restricción única
            $table->dropForeign(['id_banco']);
            $table->dropForeign(['id_comprobante_pago']);
            
            // Luego, elimina el índice único
            $table->dropUnique(['id_banco', 'id_comprobante_pago']);
            
            // Vuelve a agregar las claves foráneas si es necesario
            $table->foreign('id_banco')->references('id')->on('bank')->onDelete('restrict');
            $table->foreign('id_comprobante_pago')->references('id')->on('comprobante_pago')->onDelete('restrict');
        });
    }
};
