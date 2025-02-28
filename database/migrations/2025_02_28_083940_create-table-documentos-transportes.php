<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentacion_transp_ov', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transportes_ov_id');
            $table->foreign('transportes_ov_id')->references('id')->on('transportes_orden_venta')->onDelete('restrict');
            $table->unsignedBigInteger('comprobante_trasp_ov_id')->nullable();
            $table->foreign('comprobante_trasp_ov_id')->references('id')->on('comprobante_transp_ov')->onDelete('restrict');
            $table->string('numero_documento')->nullable();
            $table->decimal('monto');
            $table->smallInteger('n_cajas');
            $table->longText('observacion');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentacion_transp_ov');
    }
};
