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
        Schema::create('condicion_almacenamiento', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('state')->default(1)->comment('1 es activo, 0 es inactivo');
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar el valor predeterminado
        DB::table('condicion_almacenamiento')->insert([
            'name' => 'NO MAYOR A 30° C',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicion_almacenamiento');
    }
};
