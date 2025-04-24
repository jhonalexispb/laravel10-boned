<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guia_devolucion_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guia_devolucion_id');
            $table->foreign('guia_devolucion_id')->references('id')->on('guia_devolucion')->onDelete('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->integer('cantidad');
            $table->unsignedBigInteger('lote_id')->nullable();
            $table->foreign('lote_id')->references('id')->on('producto_lote_relation')->onDelete('restrict');
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
        Schema::dropIfExists('guia_devolucion_detail');
    }
};
