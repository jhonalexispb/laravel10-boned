<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedor')->onDelete('restrict');
            $table->unsignedBigInteger('type_comprobante_compra_id');
            $table->foreign('type_comprobante_compra_id')->references('id')->on('type_comprobante_pago_compra')->onDelete('restrict');
            $table->unsignedBigInteger('forma_pago_id');
            $table->foreign('forma_pago_id')->references('id')->on('forma_pago_ordenes_compra')->onDelete('restrict');
            $table->boolean('igv_state')->comment('1 los precios de compra incluyen igv y 0 los precios de compra no incluyen igv');
            $table->dateTime('date_recepcion')->nullable()->comment('fecha que indica el dia en el que se recibio la mercaderia');
            $table->dateTime('date_revision')->nullable()->comment('fecha que indica el dia en el que se ingreso la mercaderia al stock');
            $table->longText('descripcion')->nullable();
            $table->boolean('notificacion')->comment('¿Notificar a los vendedores?');
            $table->longText('mensaje_notificacion')->nullable();
            $table->decimal('importe',8,2)->default(0);
            $table->decimal('igv',8,2)->default(0);
            $table->decimal('total',8,2)->default(0);
            $table->dateTime('fecha_ingreso')->nullable();
            $table->tinyInteger('state')->default(0)->comment('0 es solicitado, 1 es recepcionado, 2 es revisado, 3 parcial, 4 ingresado');
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
        Schema::dropIfExists('ordenes_compra');
    }
};
