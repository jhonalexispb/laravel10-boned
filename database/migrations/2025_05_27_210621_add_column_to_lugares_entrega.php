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
        Schema::table('lugares_de_entrega', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('longitud');
            $table->string('imagen_public_id')->nullable()->after('imagen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lugares_de_entrega', function (Blueprint $table) {
            $table->dropColumn(['imagen', 'imagen_public_id']);
        });
    }
};
