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
        Schema::create('warehouses', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->boolean('state')->default(1);

            $table->unsignedBigInteger('sucursale_id')->nullable(); // Esto define la columna que será la clave foránea
            $table->foreign('sucursale_id')->references('id')->on('sucursales')->onDelete('restrict'); // Define la relación con la tabla 'sucursales'
            
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
