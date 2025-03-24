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
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('afectacion_igv_id')
                ->nullable()
                ->after('codigo_digemid') // Agrega la columna después de codigo_digemid
                ->constrained('afectaciones_igv')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // Asignar el ID de afectación IGV por defecto a los productos existentes
        DB::table('productos')->whereNull('afectacion_igv_id')->update(['afectacion_igv_id' => 1]);

        // Convertir la columna a NOT NULL
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('afectacion_igv_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['afectacion_igv_id']); // Eliminar la clave foránea
            $table->dropColumn('afectacion_igv_id'); // Eliminar la columna correctamente
        });
    }
};
