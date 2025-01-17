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
        Schema::create('sucursales_estado_digemid', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            // Relación con la tabla 'cliente_sucursales' (una sucursal)
            $table->foreign('sucursal_id')->references('id')->on('cliente_sucursales')->onDelete('cascade');
        
            // Relación con las tablas de estados de sucursal, todas deben ser nullable si una sola debe estar activa a la vez
            $table->unsignedBigInteger('activo')->nullable();  // Relación con 'sucursales_activas'
            $table->foreign('activo')->references('id')->on('sucursales_activas')->onDelete('cascade');
            
            $table->unsignedBigInteger('cierre_definitivo')->nullable();  // Relación con 'sucursales_cierre_definitivo'
            $table->foreign('cierre_definitivo')->references('id')->on('sucursales_cierre_definitivo')->onDelete('cascade');
            
            $table->unsignedBigInteger('cierre_temporal')->nullable();  // Relación con 'sucursales_cierre_temporal'
            $table->foreign('cierre_temporal')->references('id')->on('sucursales_cierre_temporal')->onDelete('cascade');
            
            $table->unsignedBigInteger('sin_registro_digemid')->nullable();  // Relación con 'sucursales_sin_registro_digemid'
            $table->foreign('sin_registro_digemid')->references('id')->on('sucursales_sin_registro_digemid')->onDelete('cascade');
            
            $table->unsignedBigInteger('persona_natural')->nullable();  // Relación con 'sucursales_persona_natural'
            $table->foreign('persona_natural')->references('id')->on('sucursales_persona_natural')->onDelete('cascade');
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales_estado_digemid');
    }
};
