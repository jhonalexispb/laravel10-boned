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
        Schema::create('formas_facturacion_cliente', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('dias');
            $table->boolean('state')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar el valor predeterminado
        DB::table('formas_facturacion_cliente')->insert([
            'nombre' => 'NORMAL',
            'dias' => 30,
            'state' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formas_facturacion_cliente');
    }
};
