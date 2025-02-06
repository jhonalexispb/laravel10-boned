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
        Schema::table('productos', function (Blueprint $table) {
            // Agregar columna 'caracteristicas' después de la columna 'nombre'
            $table->text('caracteristicas')->nullable()->after('nombre');
            $table->string('codigo_digemid')->nullable()->after('registro_sanitario');
            $table->boolean('maneja_lotes')->default(1)->after('sale_boleta')->comment('1 maneja lotes 0 no maneja lotes');
            $table->boolean('maneja_escalas')->default(0)->after('maneja_lotes')->comment('1 maneja escalas 0 no maneja escalas');
            $table->boolean('promocionable')->default(0)->after('maneja_escalas')->comment('1 es promocionable 0 no es promocionable');

            // Agregar columna 'categoria_id' después de 'caracteristicas' y establecer la clave foránea
            $table->unsignedBigInteger('categoria_id')->nullable()->after('caracteristicas');
            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn(['caracteristicas', 'codigo_digemid', 'maneja_lotes', 'maneja_escalas', 'promocionable', 'categoria_id']);
        });
    }
};
