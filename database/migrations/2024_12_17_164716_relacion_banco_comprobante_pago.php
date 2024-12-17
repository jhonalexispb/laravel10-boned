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
        Schema::create('relacion_bank_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idBanco')->constrained('bank')->onDelete('restrict');
            $table->foreignId('idComprobantePago')->constrained('comprobante_pago')->onDelete('restrict');
            $table->string('tipoCaracter', 50);
            $table->integer('ncaracteres');
            $table->string('nombre')->nullable();
            $table->string('ubicacionCodigo')->nullable();
            $table->string('imgEjemplo')->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relacion_bank_comprobante');
    }
};
