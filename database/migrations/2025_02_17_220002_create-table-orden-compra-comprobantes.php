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
        Schema::create('ordenes_compra_n_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_compra_id');
            $table->foreign('orden_compra_id')->references('id')->on('ordenes_compra')->onDelete('restrict');
            $table->string('serie');
            $table->string('n_documento');
            $table->decimal('importe',8,2);
            $table->decimal('igv',8,2);
            $table->decimal('total',8,2);
            $table->tinyInteger('state')->default(0)->comment('0 es conforme, 1 es anulado, 2 es nota de credito');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('ordenes_compra_n_comprobantes');
    }
};
