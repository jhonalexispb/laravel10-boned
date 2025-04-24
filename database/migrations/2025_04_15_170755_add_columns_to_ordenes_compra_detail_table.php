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
        Schema::table('ordenes_compra_detail', function (Blueprint $table) {
            $table->integer('cantidad_reemplazo')->nullable()->comment('cantidad que reemplaza a la cantidad original en caso vengan mas de lo solicitado');;
            $table->integer('cantidad_pendiente')->comment('cantidad que se disminuira para indicar cuantos productos hay que gestionar');
            $table->boolean('devolver')->default(0)->comment('0 no se generara guia devolucion, 1 se generara guia devolucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_compra_detail', function (Blueprint $table) {
            $table->dropColumn('cantidad_reemplazo');
            $table->dropColumn('cantidad_pendiente');
            $table->dropColumn('devolver');
        });
    }
};
