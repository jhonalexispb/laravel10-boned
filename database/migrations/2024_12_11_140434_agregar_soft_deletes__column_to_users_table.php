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
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname',50)->nullable();
            $table->string('phone',25)->nullable();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('sucursal_id')->nullable();
            $table->string('n_document',25)->nullable();
            $table->string('avatar', 250)->nullable();
            $table->string('gender',15)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
