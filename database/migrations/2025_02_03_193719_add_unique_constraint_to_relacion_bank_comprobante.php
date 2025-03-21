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
        Schema::table('relacion_bank_comprobante', function (Blueprint $table) {
            $table->unique(['id_banco', 'id_comprobante_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relacion_bank_comprobante', function (Blueprint $table) {
            $table->dropUnique(['id_banco', 'id_comprobante_pago']);
        });
    }
};
