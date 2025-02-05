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
            $table->dropColumn(['caracteristicas', 'categoria_id']);
        });
    }
};
