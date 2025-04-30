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
        Schema::table('productos', function (Blueprint $table) {
            $table->string('stock_vendedor')->default(0)->after('stock');
            $table->tinyInteger('state_stock_vendedor',false,true)->default(3)->comment('1 es disponible y 2 es por agotar 3 agotado')->after('state_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_vendedor');
            $table->dropColumn('state_stock_vendedor');
        });
    }
};
