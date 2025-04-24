<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_comprobante_serie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_comprobante_id');
            $table->foreign('type_comprobante_id')->references('id')->on('type_comprobante')->onDelete('cascade');
            $table->string('serie');
            $table->unsignedInteger('correlativo');
            $table->boolean('state')->default(0)->comment('0 es inactivo, 1 es activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('type_comprobante_serie');
    }
};
