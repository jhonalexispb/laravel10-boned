<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('afectaciones_igv', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique(); // Código SUNAT (ej. "10", "20", "30")
            $table->string('descripcion'); // Nombre descriptivo
            $table->text('detalle')->nullable(); // Explicación adicional
            $table->timestamps();
        });

        DB::table('afectaciones_igv')->insert([
            ['codigo' => '10', 'descripcion' => 'Gravado - Operación Onerosa', 'detalle' => 'Producto con IGV (18%)'],
            ['codigo' => '20', 'descripcion' => 'Exonerado - Operación Onerosa', 'detalle' => 'Medicamentos exonerados según la ley'],
            ['codigo' => '30', 'descripcion' => 'Inafecto - Operación Onerosa', 'detalle' => 'Servicios como colocación de inyectables'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afectaciones_igv'); // Elimina la tabla si se revierte la migración
    }
};
