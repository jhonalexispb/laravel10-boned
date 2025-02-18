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
        Schema::create('type_comprobante_pago_compra', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('state')->default(1)->comment('1 es activo y 0 es inactivo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('type_comprobante_pago_compra')->insert([
            'name' => 'FACTURA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_comprobante_pago_compra');
    }
};
