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
        Schema::create('guias_prestamo_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guia_prestamo_id');
            $table->foreign('guia_prestamo_id')->references('id')->on('guias_prestamo')->onDelete('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->unsignedBigInteger('lote_id');
            $table->foreign('lote_id')->references('id')->on('producto_lote_relation')->onDelete('restrict');
            $table->integer('cantidad');
            $table->integer('stock');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias_prestamo_detalle');
    }
};
