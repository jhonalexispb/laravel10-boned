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
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            $table->unsignedBigInteger('modo_facturacion_id')->default(1)->after('state');
            $table->foreign('modo_facturacion_id')->references('id')->on('formas_facturacion_cliente')->after('modo_facturacion_id');
            $table->integer('dias',false,true)->default(30)->after('modo_facturacion_id');
            $table->enum('formaPago',["1","2","3"])->default('2')->comment('1 credito, 2 contado, 3 credito/contado')->after('dias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            // Eliminar la clave foránea
            $table->dropForeign(['modo_facturacion_id']);

            // Eliminar las columnas agregadas
            $table->dropColumn(['modo_facturacion_id', 'dias', 'formaPago']);
        });
    }
};
