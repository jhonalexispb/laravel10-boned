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
        Schema::table('ordenes_compra_detail', function (Blueprint $table) {
            $table->dropForeign(['n_comprobante_id']);
            $table->dropColumn('n_comprobante_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_compra_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('n_comprobante_id')->nullable();
            
            // Restaurar la foreign key
            $table->foreign('n_comprobante_id')
                ->references('id')
                ->on('ordenes_compra')
                ->onDelete('cascade');
        });
    }
};
