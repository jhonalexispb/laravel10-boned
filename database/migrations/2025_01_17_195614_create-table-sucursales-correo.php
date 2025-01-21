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
        Schema::create('correos_sucursales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruc_id');
            $table->foreign('ruc_id')->references('id')->on('clienteruc')->onDelete('cascade');
            $table->unsignedBigInteger('cliente_sucursal_id');
            $table->foreign('cliente_sucursal_id')->references('id')->on('cliente_sucursales')->onDelete('cascade');
            $table->unsignedBigInteger('correo_id');
            $table->foreign('correo_id')->references('id')->on('correos_clientes')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correos_sucursales');
    }
};
