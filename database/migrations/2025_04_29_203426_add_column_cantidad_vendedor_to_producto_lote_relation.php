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
        Schema::table('producto_lote_relation', function (Blueprint $table) {
            $table->string('cantidad_vendedor')->default(0)->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_lote_relation', function (Blueprint $table) {
            $table->dropColumn('cantidad_vendedor');
        });
    }
};
