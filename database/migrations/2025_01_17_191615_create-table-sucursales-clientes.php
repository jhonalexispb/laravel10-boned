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
        Schema::create('cliente_sucursales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruc_id');
            $table->foreign('ruc_id')->references('id')->on('clienteruc')->onDelete('restrict');
            $table->string('nombre_comercial');
            $table->text('direccion');
            $table->unsignedBigInteger('distrito');
            $table->foreign('distrito')->references('id')->on('distritos')->onDelete('restrict');
            $table->decimal('deuda',10,1,true)->default(0);
            $table->decimal('linea_credito',10,1,true)->default(0);
            $table->boolean('modo_trabajo')->default(1)->comment('0 es secundarios y 1 es principal');
            $table->unsignedBigInteger('categoria_digemid_id');
            $table->foreign('categoria_digemid_id')->references('id')->on('categorias_digemid')->onDelete('restrict');
            $table->unsignedBigInteger('estado_digemid');
            $table->foreign('estado_digemid')->references('id')->on('estados_digemid')->onDelete('restrict');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('nregistro_id')->nullable();
            $table->foreign('nregistro_id')->references('id')->on('registros_digemid')->onDelete('restrict');
            $table->string('documento_en_proceso')->nullable()->comment('documento que indica que el cliente esta en proceso de inspeccion');
            $table->boolean('state')->default(1)->comment('0 es inactivo y 1 es activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_sucursales');
    }
};
