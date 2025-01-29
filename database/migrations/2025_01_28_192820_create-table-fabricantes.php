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
        Schema::create('fabricantes_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('pais')->nullable();
            $table->boolean('status')->default(1);
            $table->string('imagen')->nullable();
            $table->string('imagen_public_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fabricantes_producto');
    }
};
