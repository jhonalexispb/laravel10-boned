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
        Schema::create('orden_venta', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('cliente_sucursales')->onDelete('restrict');
            $table->unsignedBigInteger('comprobante_id')->nullable();
            $table->foreign('comprobante_id')->references('id')->on('comprobante_orden_venta')->onDelete('restrict');
            $table->decimal('total')->default(0);
            $table->boolean('formaPago')->default(1)->comment('0 es contado, 1 es credito');
            $table->unsignedBigInteger('forma_facturacion_id')->nullable();
            $table->foreign('forma_facturacion_id')->references('id')->on('formas_facturacion_cliente')->onDelete('restrict');
            $table->longText('comentario')->nullable();

            $table->boolean('zonaReparto')->nullable()->comment('0 es local, 1 es previncia');
            $table->unsignedBigInteger('transporte_id')->nullable();
            $table->foreign('transporte_id')->references('id')->on('transportes_orden_venta')->onDelete('restrict');

            $table->tinyInteger('state_orden')->default(0)->comment('0: Pendiente, 1: Enviado, 2: Facturado');
            $table->dateTime("fecha_envio")->nullable();
            $table->dateTime("fecha_facturacion")->nullable();

            $table->tinyInteger('state_fisico')->default(0)->comment('0: En almacen, 1: Empaquetado, 3:En ruta ,4: En agencia, 5: Entregado al cliente, 6: cancelado, 7 devuelto');
            $table->dateTime("fecha_empaquetado")->nullable();
            $table->dateTime("fecha_cargado")->nullable();
            $table->dateTime("fecha_agencia")->nullable();
            $table->dateTime("fecha_entregado_cliente")->nullable(); 

            $table->boolean('doc_pend_agencia')->nullable()->comment('0: En agencia, 1: Regularizado');
            $table->dateTime("fecha_documentacion_entregada")->nullable(); //cuando sea provincia y el transporte devuelva las guias firmadas

            $table->tinyInteger('state_seguimiento')->default(0)->comment('0: No revisado, 1: Chequeado');
            $table->dateTime("fecha_corroboracion")->nullable();

            $table->unsignedBigInteger('documento_transporte_id')->nullable();
            $table->foreign('documento_transporte_id')->references('id')->on('documentacion_transp_ov')->onDelete('set null');

            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_venta');
    }
};
