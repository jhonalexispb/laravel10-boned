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
            $table->string('celular',9)->nullable();
            $table->string('correo')->nullable();
            $table->decimal('latitud', 10, 6)->nullable();
            $table->decimal('longitud', 10, 6)->nullable();
            $table->decimal('deuda',10,1,true)->default(0);
            $table->decimal('linea_credito',10,1,true)->default(0);
            $table->boolean('modo_trabajo')->default(1)->comment('0 es secundarios y 1 es principal');
            $table->unsignedBigInteger('categoria_digemid_id');
            $table->foreign('categoria_digemid_id')->references('id')->on('categorias_digemid')->onDelete('restrict');
            $table->boolean('estado_digemid')->comment('1 activo, 2 cierre temporal, 3 cierre definitivo, 4 sin registro digemid, 5 persona natural');
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
