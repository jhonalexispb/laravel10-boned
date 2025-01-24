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
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            $table->string('image_public_id')->nullable()->after('image');  // Aquí 'image' es para almacenar el public_id de la imagen
            $table->string('documento_en_proceso_public_id')->nullable()->after('documento_en_proceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente_sucursales', function (Blueprint $table) {
            $table->dropColumn('image_public_id');
            $table->dropColumn('documento_en_proceso_public_id');
        });
    }
};
