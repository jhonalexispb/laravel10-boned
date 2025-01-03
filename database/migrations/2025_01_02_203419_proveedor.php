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
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('razonSocial');
            $table->string('name')->unique();
            $table->string('address')->nullable();
            $table->foreignId('iddistrito')->nullable()->constrained('distritos')->onDelete('restrict');
            $table->foreignId('idrepresentante')->nullable()->constrained('representante_proveedor')->onDelete('restrict');
            $table->string('email')->nullable();
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
        Schema::dropIfExists('proveedor');
    }
};
