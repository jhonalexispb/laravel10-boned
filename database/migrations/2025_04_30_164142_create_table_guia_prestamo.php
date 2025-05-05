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
        Schema::create('guias_prestamo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('user_encargado_id')->nullable();
            $table->foreign('user_encargado_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            $table->longText('comentario')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->dateTime('fecha_gestionado')->nullable()->comment('fecha en la que se reviso que termino de venderse la mercaderia pendiente');
            $table->dateTime('fecha_revisado')->nullable()->comment('fecha en la que se reviso que todo este conforme, mas que todo las ventas y los pagos');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('state')->default(0)->comment('0 es proceso de creacion, 1 es pendiente, 2 es entregado, 3 es en proceso de venta, 4 es gestionado y 5 es revisado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias_prestamo');
    }
};
