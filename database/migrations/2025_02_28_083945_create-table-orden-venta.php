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
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('cliente_sucursales')->onDelete('restrict');
            $table->unsignedBigInteger('comprobante_id')->nullable();
            $table->foreign('comprobante_id')->references('id')->on('orden_venta_type_comprobante')->onDelete('restrict');
            $table->decimal('total',10,2)->default(0);
            $table->boolean('forma_pago')->nullable()->comment('0 es contado, 1 es credito');
            $table->longText('comentario')->nullable();

            $table->boolean('zona_reparto')->nullable()->comment('0 es local, 1 es provincia');
            $table->unsignedBigInteger('transporte_id')->nullable();
            $table->foreign('transporte_id')->references('id')->on('transportes_orden_venta')->onDelete('restrict');

            $table->tinyInteger('state_orden')->default(0)->comment('0: Cotizacion, 1: Enviado, 2:Facturado, 3:Anulado');
            $table->dateTime("fecha_envio")->nullable();
            $table->dateTime("fecha_creacion_comprobante")->nullable();

            $table->tinyInteger('estado_pago')->default(0)->comment('0: Sin pagar, 1: Parcial, 2: Pagado total');
            $table->decimal('monto_pagado', 10, 2)->default(0);

            $table->tinyInteger('state_fisico')->default(0)->comment('0:Cotizacion, 1: En almacen, 2: Empaquetado, 3:En ruta ,4: En agencia, 5: Entregado al cliente, 6: cancelado, 7 devuelto');
            $table->dateTime("fecha_empaquetado")->nullable();
            $table->dateTime("fecha_cargado")->nullable();
            $table->dateTime("fecha_agencia")->nullable();
            $table->dateTime("fecha_entregado_cliente")->nullable(); 

            $table->tinyInteger('state_seguimiento')->default(0)->comment('0: No revisado, 1: Chequeado');
            $table->dateTime("fecha_corroboracion")->nullable();

            $table->unsignedBigInteger('documento_transporte_id')->nullable();
            $table->foreign('documento_transporte_id')->references('id')->on('documentacion_transp_ov')->onDelete('set null')->comment('Se usa cuando el cliente no va a pagar el transporte, esto es el documento que entrega la empresa de transportes');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
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
