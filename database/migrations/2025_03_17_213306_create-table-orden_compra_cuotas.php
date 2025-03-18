<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra_cuotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_compra_id');
            $table->foreign('orden_compra_id')->references('id')->on('ordenes_compra')->onDelete('restrict');
            $table->decimal('amount',10,2);
            $table->boolean('state')->default(0)->comment('0 es pendiente, 1 es cancelado');
            $table->dateTime('start');
            $table->dateTime('reminder');
            $table->longText('notes')->nullable();
            $table->string('numero_unico')->nullable();
            $table->dateTime('fecha_cancelado')->nullable();
            $table->boolean('notificado')->default(0)->comment('0 es pendiente, y 1 es notificado');
            $table->integer('intentos_envio')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['numero_unico'], 'unique_numero_unico')->whereNotNull('numero_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra_cuotas');
    }
};
