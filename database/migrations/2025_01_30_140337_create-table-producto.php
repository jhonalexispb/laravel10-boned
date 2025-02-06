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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku','8');
            $table->enum('tproducto',['1','2'])->comment('1 es comercial y 2 generico');
            $table->string('codigobarra');
            $table->unsignedBigInteger('unidad_id');
            $table->foreign('unidad_id')->references('id')->on('unidades')->onDelete('restrict');
            $table->unsignedBigInteger('laboratorio_id');
            $table->foreign('laboratorio_id')->references('id')->on('laboratorio')->onDelete('restrict');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('registro_sanitario')->nullable();
            $table->decimal('pventa',10,1,true)->default(0);
            $table->decimal('pcompra',10,1,true)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_seguridad')->default(0);
            $table->string('imagen')->nullable();
            $table->unsignedBigInteger('linea_farmaceutica_id');
            $table->foreign('linea_farmaceutica_id')->references('id')->on('lineas_farmaceuticas')->onDelete('restrict');
            $table->unsignedBigInteger('fabricante_id');
            $table->foreign('fabricante_id')->references('id')->on('fabricantes_producto')->onDelete('restrict');
            $table->boolean('sale_boleta')->default(0)->comment('1 sale en boleta y 0 no sale en boleta');
            $table->boolean('state')->default(1)->comment('1 es activo y 0 es inactivo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
