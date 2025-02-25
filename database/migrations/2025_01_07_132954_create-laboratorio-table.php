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
        Schema::create('laboratorio', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('imagen')->nullable();
            $table->string('color')->default('#4145ff');
            $table->boolean('state')->default(1);
            $table->decimal('margen_minimo')->default(20);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorio');
    }
};
