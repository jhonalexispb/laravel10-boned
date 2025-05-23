<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_venta_type_comprobante', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('name')->unique();
            $table->boolean('venta');
            $table->tinyInteger('state')->default(1)->comment('0 es inactivo, 1 es activo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('orden_venta_type_comprobante')->insert([
            [   
                'id' => 1,
                'codigo' => '00',
                'name' => 'ORDEN DE COMPRA',
                'venta' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'id' => 2,
                'codigo' => '01',
                'name' => 'FACTURA',
                'venta' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'id' => 3,
                'codigo' => '03',
                'name' => 'BOLETA',
                'venta' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'id' => 4,
                'codigo' => '07',
                'name' => 'NOTA CREDITO',
                'venta' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'id' => 5,
                'codigo' => '08',
                'name' => 'NOTA DEBITO',
                'venta' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'id' => 6,
                'codigo' => '09',
                'name' => 'GUIA REMISION',
                'venta' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'codigo' => 'B',
                'name' => 'T & M',
                'venta' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_venta_type_comprobante');
    }
};