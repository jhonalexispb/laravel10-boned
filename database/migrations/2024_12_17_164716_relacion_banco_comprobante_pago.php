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
        Schema::create('relacion_bank_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_banco')->constrained('bank')->onDelete('restrict');
            $table->foreignId('id_comprobante_pago')->constrained('comprobante_pago')->onDelete('restrict');
            $table->boolean('tipo_caracter')->default(1)->comment('1 es numeros, y 2 es numeros y letras');
            $table->integer('ncaracteres');
            $table->string('ubicacion_codigo')->nullable();
            $table->string('img_ejemplo')->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relacion_bank_comprobante', function (Blueprint $table) {
            // 🔹 Primero elimina las claves foráneas
            $table->dropForeign(['id_banco']);
            $table->dropForeign(['id_comprobante_pago']);
        });

        // 🔹 Luego elimina la tabla
        Schema::dropIfExists('relacion_bank_comprobante');
    }
};
