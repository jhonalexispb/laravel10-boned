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
        Schema::create('orden_venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_venta_id');
            $table->foreign('order_venta_id')->references('id')->on('orden_venta')->onDelete('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->unsignedBigInteger('lote_id');
            $table->foreign('lote_id')->references('id')->on('producto_lote_relation')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('pventa', 10, 2);
            $table->decimal('total', 10, 2);                
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
        Schema::dropIfExists('orden_venta_detalle');
    }
};
