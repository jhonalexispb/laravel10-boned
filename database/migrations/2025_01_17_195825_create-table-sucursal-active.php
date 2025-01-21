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
        Schema::create('sucursales_activas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_sucursal_id');
            $table->foreign('cliente_sucursal_id')->references('id')->on('cliente_sucursales')->onDelete('cascade');
            $table->unsignedBigInteger('nregistro_id');
            $table->foreign('nregistro_id')->references('id')->on('registros_digemid')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales_activas');
    }
};
