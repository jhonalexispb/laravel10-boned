<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comp_ov_estdig_relation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comp_ov_id');
            $table->foreign('comp_ov_id')->references('id')->on('orden_venta_type_comprobante')->onDelete('cascade');
            $table->unsignedBigInteger('esta_dig_id');
            $table->foreign('esta_dig_id')->references('id')->on('estados_digemid')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('comp_ov_estdig_relation')->insert([
            [
                'comp_ov_id' => 2,
                'esta_dig_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 2,
                'esta_dig_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 3,
                'esta_dig_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 3,
                'esta_dig_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 3,
                'esta_dig_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 3,
                'esta_dig_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'comp_ov_id' => 7,
                'esta_dig_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comp_ov_estdig_relation');
    }
};
