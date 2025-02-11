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
        Schema::create('catalogo_productos_farmaceuticos_digemid', function (Blueprint $table) {
            $table->id();
            $table->string('cod_prod')->nullable();
            $table->string('nom_prod')->nullable();
            $table->string('concent')->nullable();
            $table->string('nom_form_farm')->nullable();
            $table->string('presentac')->nullable();
            $table->string('fraccion')->nullable();
            $table->string('num_regsan')->nullable();
            $table->string('nom_titular')->nullable();
            $table->string('nom_fabricante')->nullable();
            $table->string('nom_ifa')->nullable();
            $table->string('nom_rubro')->nullable();
            $table->string('situacion')->nullable();
            $table->timestamps();

            $table->index('num_regsan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_productos_farmaceuticos_digemid');
    }
};
