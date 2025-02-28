<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_transp_ov', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->tinyInteger('state')->default(1)->comment('0 es inactivo, 1 es activo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('comprobante_transp_ov')->insert([
            [
                'name' => 'FACTURA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BOLETA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'POSIT',
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
        Schema::dropIfExists('comprobante_transp_ov');
    }
};
