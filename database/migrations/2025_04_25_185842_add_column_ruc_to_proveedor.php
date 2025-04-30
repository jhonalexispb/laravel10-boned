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
        Schema::table('proveedor', function (Blueprint $table) {
            $table->string('ruc',11)->nullable()->after('razonSocial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            $table->dropColumn('ruc');
        });
    }
};
