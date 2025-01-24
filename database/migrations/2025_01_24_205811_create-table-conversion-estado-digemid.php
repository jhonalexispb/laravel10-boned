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
        Schema::create('conversion_estados_digemid', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estado_digemid_id');
            $table->foreign('estado_digemid_id')->references('id')->on('estados_digemid');
            $table->unsignedBigInteger('transform_estado_digemid_id');
            $table->foreign('transform_estado_digemid_id')->references('id')->on('estados_digemid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_estados_digemid');
    }
};
