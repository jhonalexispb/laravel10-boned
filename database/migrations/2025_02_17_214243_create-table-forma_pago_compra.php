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
        Schema::create('forma_pago_ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('state')->default(1)->comment('1 es activo y 0 es inactivo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('forma_pago_ordenes_compra')->insert([
            [
                'name' => 'LETRAS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CARTERA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CONTADO',
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
        Schema::dropIfExists('forma_pago_ordenes_compra');
    }
};
