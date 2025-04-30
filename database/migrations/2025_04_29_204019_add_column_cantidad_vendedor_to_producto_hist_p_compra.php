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
        Schema::table('producto_hist_p_venta', function (Blueprint $table) {
            // Asegúrate de usar el tipo de dato correcto según el ID que tenga producto_lote_relation
            $table->unsignedBigInteger('order_compra_id')->nullable()->after('comentario');

            // Llave foránea (opcionalmente puedes agregar onDelete si quieres eliminar en cascada)
            $table->foreign('order_compra_id')
                ->references('id')
                ->on('ordenes_compra')
                ->onDelete('set null'); // o 'cascade', 'restrict', etc.
            $table->index('order_compra_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_hist_p_venta', function (Blueprint $table) {
            $table->dropForeign(['order_compra_id']);
            $table->dropIndex(['order_compra_id']);
            $table->dropColumn('order_compra_id');
        });
    }
};
