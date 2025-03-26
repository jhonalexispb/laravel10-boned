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
        Schema::create('ordenes_compra_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_compra_id');
            $table->foreign('orden_compra_id')->references('id')->on('ordenes_compra')->onDelete('cascade');
            $table->unsignedBigInteger('n_comprobante_id')->nullable();
            $table->foreign('n_comprobante_id')->references('id')->on('ordenes_compra')->onDelete('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('p_compra',8,2);
            $table->decimal('total',8,2);
            $table->decimal('margen_ganancia',8,2);
            $table->decimal('p_venta',8,2);
            $table->boolean('condicion_vencimiento')->comment('0 es mayor o igual y 1 es igual a');
            $table->boolean('bonificacion')->comment('0 no y 1 si');
            $table->date('fecha_vencimiento');
            $table->tinyInteger('state')->default(0)->comment('0 es solicitado, 1 es ingresado, 2 es rechazado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra_detail');
    }
};
