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
        // Paso 1: Crear la columna 'codigo' sin la restricción 'unique'
        Schema::table('laboratorio', function (Blueprint $table) {
            $table->string('codigo')->after('id');
        });

        // Paso 2: Asignar valores únicos a la columna 'codigo'
        $laboratorios = DB::table('laboratorio')->get();
        foreach ($laboratorios as $laboratorio) {
            DB::table('laboratorio')
                ->where('id', $laboratorio->id)
                ->update(['codigo' => 'codigo_' . $laboratorio->id]);
        }

        // Paso 3: Agregar la restricción 'unique' a la columna 'codigo'
        Schema::table('laboratorio', function (Blueprint $table) {
            $table->unique('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboratorio', function (Blueprint $table) {
            // Eliminar la columna 'codigo'
            $table->dropColumn('codigo');
        });
    }
};
