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
        Schema::create('laboratorio_proveedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laboratorio_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('margen_minimo')->default(20);
            $table->timestamps();

            $table->foreign('laboratorio_id')->references('id')->on('laboratorio')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('proveedor')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorio_proveedor');
    }
};
