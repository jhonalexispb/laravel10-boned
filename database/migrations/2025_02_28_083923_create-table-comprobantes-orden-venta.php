<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_orden_venta', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('name')->unique();
            $table->tinyInteger('state')->default(1)->comment('0 es inactivo, 1 es activo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('comprobante_orden_venta')->insert([
            [   
                'codigo' => '00',
                'name' => 'NOTA DE PEDIDO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => '01',
                'name' => 'FACTURA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => '03',
                'name' => 'BOLETA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => '07',
                'name' => 'NOTA CREDITO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => '08',
                'name' => 'NOTA DEBITO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => '09',
                'name' => 'GUIA REMISION',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'codigo' => 'A',
                'name' => 'GUIA PRESTAMO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'B',
                'name' => 'T & M',
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
        Schema::dropIfExists('comprobante_orden_venta');
    }
};