<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guia_devolucion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_comp_serie_id');
            $table->foreign('type_comp_serie_id')->references('id')->on('type_comprobante_serie')->onDelete('restrict');
            $table->unsignedBigInteger('correlativo');
            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedor')->onDelete('restrict');
            $table->unsignedBigInteger('order_compra_id')->nullable();
            $table->foreign('order_compra_id')->references('id')->on('ordenes_compra')->onDelete('cascade');
            $table->dateTime('date_justificado')->nullable()->comment('fecha que indica el dia en el que se adjunto la nota de credito de la guia');
            $table->longText('descripcion')->nullable();
            $table->boolean('state')->default(0)->comment('0 es solicitado, 1 es solventado');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guia_devolucion');
    }
};
