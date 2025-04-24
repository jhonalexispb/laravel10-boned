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
        Schema::create('order_compra_detail_gestionado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_compra_id');
            $table->foreign('orden_compra_id')->references('id')->on('ordenes_compra')->onDelete('restrict');
            $table->unsignedBigInteger('oc_n_comprob_id');
            $table->foreign('oc_n_comprob_id')->references('id')->on('ordenes_compra_n_comprobantes')->onDelete('restrict');
            $table->unsignedBigInteger('afectacion_id');
            $table->foreign('afectacion_id')->references('id')->on('afectaciones_igv')->onDelete('restrict');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('total',8,2);
            $table->boolean('bonificacion');
            $table->longText('comentario')->nullable();
            $table->unsignedDecimal('pcompra');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('order_compra_detail_gestionado');
    }
};
