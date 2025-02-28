<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {   
        Schema::create('transportes_orden_venta', function (Blueprint $table) {
            $table->id();
            $table->string('ruc')->nullable();
            $table->string('razonSocial')->nullable();
            $table->string('name');
            $table->string('direccion')->nullable();
            $table->string('celular')->nullable();
            $table->decimal('latitud', 10, 6)->nullable();
            $table->decimal('longitud', 10, 6)->nullable();
            $table->boolean('solicita_guia')->default(1)->comment('1 es activo, 0 es inactivo');
            $table->boolean('state')->default(1)->comment('1 es activo, 0 es inactivo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportes_orden_venta');
    }
};