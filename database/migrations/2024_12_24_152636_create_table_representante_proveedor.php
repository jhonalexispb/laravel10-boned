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
        Schema::create('representante_proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('celular',9)->nullable();
            $table->string('telefono',15)->nullable();
            $table->string('email')->unique();
            $table->boolean('state')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('representante_proveedor');
    }
};
