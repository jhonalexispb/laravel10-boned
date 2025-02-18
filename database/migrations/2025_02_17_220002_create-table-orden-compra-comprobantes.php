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
        Schema::create('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_comprobante_compra_id');
            $table->foreign('type_comprobante_compra_id')->references('id')->on('type_comprobante_pago_compra')->onDelete('cascade');
            $table->string('serie');
            $table->string('n_documento');
            $table->decimal('importe',8,2);
            $table->decimal('igv',8,2);
            $table->decimal('total',8,2);
            $table->tinyInteger('state')->default(0)->comment('0 es anulado, 1 es conforme, 2 es nota de credito');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra_n_comprobantes');
    }
};
