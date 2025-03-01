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
        Schema::create('estados_digemid', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('estados_digemid')->insert([
            [
                'nombre' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CIERRE TEMPORAL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CIERRE DEFINITIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'SIN REGISTRO DIGEMID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'PERSONA NATURAL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'EN PROCESO DE INSPECCION',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_digemid');
    }
};
