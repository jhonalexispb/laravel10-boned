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
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            // Modificar la columna para que acepte nulos
            $table->unsignedBigInteger('categoria_digemid_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            // Restaurar la columna a su estado original sin permitir nulos
            $table->unsignedBigInteger('categoria_digemid_id')->nullable(false)->change();
        });
    }
};
