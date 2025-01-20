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
            $table->string('nregistro',7)->unique();
            $table->unsignedBigInteger('correo_id');
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
        Schema::dropIfExists('sucursales_activas');
    }
};
