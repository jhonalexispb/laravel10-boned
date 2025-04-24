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
        Schema::create('type_comprobante', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique();
            $table->string('nombre');
            $table->boolean('state')->default('1')->comment('0 es inacativo 1 activo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('type_comprobante')->insert([
            ['codigo' => '00', 'nombre' => 'NOTA DE PEDIDO'],
            ['codigo' => '01', 'nombre' => 'FACTURA'],
            ['codigo' => '03', 'nombre' => 'BOLETA'],
            ['codigo' => '07', 'nombre' => 'NOTA DE CREDITO'],
            ['codigo' => '08', 'nombre' => 'NOTA DE DEBITO'],
            ['codigo' => '09', 'nombre' => 'GUIA DE REMISION'],
            ['codigo' => 'GP', 'nombre' => 'GUIA PRESTAMO'],
            ['codigo' => 'GD', 'nombre' => 'GUIA DE DEVOLUCION'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_comprobante');
    }
};
