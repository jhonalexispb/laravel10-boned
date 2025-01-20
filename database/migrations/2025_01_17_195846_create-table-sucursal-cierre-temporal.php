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
        Schema::create('sucursales_cierre_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dni_id');
            $table->foreign('dni_id')->references('id')->on('dni_sucursales')->onDelete('restrict');
            $table->unsignedBigInteger('correo_id')->nullable();
            $table->foreign('correo_id')->references('id')->on('correos_sucursales')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales_cierre_temporal');
    }
};
