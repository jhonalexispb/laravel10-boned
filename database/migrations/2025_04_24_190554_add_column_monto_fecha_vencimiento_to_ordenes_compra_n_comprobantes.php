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
        Schema::table('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->after('fecha_emision');
            $table->decimal('monto_real', 10, 2)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->dropColumn('fecha_vencimiento');
            $table->dropColumn('monto_real');
        });
    }
};
