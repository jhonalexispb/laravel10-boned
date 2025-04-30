<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_compra_detail_gestionado', function (Blueprint $table) {
            // Asegúrate de usar el tipo de dato correcto según el ID que tenga producto_lote_relation
            $table->unsignedBigInteger('prod_lote_rel_id')->after('cantidad');

            // Llave foránea (opcionalmente puedes agregar onDelete si quieres eliminar en cascada)
            $table->foreign('prod_lote_rel_id')
                ->references('id')
                ->on('producto_lote_relation')
                ->onDelete('restrict'); // o 'cascade', 'restrict', etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_compra_detail_gestionado', function (Blueprint $table) {
            $table->dropForeign(['prod_lote_rel_id']);
            $table->dropColumn('prod_lote_rel_id');
        });
    }
};
