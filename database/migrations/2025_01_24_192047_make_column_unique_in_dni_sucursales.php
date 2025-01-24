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
        Schema::table('dni_sucursales', function (Blueprint $table) {
            // Eliminar la clave foránea de la columna
            $table->dropForeign(['cliente_sucursal_id']);
            
            // Hacer la columna 'cliente_sucursal_id' única
            $table->unique('cliente_sucursal_id');
            
            // Reagregar la clave foránea, ahora con la restricción UNIQUE
            $table->foreign('cliente_sucursal_id')
                  ->references('id')
                  ->on('cliente_sucursales')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dni_sucursales', function (Blueprint $table) {
            $table->dropForeign(['cliente_sucursal_id']);

            // Quitar la restricción única
            $table->dropUnique(['cliente_sucursal_id']);

            // Volver a agregar la clave foránea original sin la restricción única
            $table->foreign('cliente_sucursal_id')
                  ->references('id')
                  ->on('cliente_sucursales')
                  ->onDelete('cascade');
        });
    }
};
