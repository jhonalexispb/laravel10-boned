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
            $table->boolean('igv_state')
                  ->after('n_documento')
                  ->comment('0: no incluye IGV, 1: incluye IGV');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->dropColumn('igv_state');
        });
    }
};
