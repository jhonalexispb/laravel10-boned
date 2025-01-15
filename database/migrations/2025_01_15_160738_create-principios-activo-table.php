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
        Schema::create('principio_activo', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('concentracion')->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'concentracion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('principio_activo');
    }
};
