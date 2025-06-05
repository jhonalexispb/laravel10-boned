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
            $table->boolean('modo_entrega')->nullable()->after('guia_prestamo_id')->comment('0 es recojo en tienda 1 es envio a domicilio');
            $table->unsignedBigInteger('lugar_entrega_id')->nullable()->after('modo_entrega');
            $table->foreign('lugar_entrega_id')
                  ->references('id')
                  ->on('lugares_de_entrega')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('orden_venta', function (Blueprint $table) {
            $table->dropColumn('modo_entrega');
            $table->dropForeign(['lugar_entrega_id']);
            $table->dropColumn('lugar_entrega_id');
        });
    }
};
