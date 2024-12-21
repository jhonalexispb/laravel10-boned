<?php

namespace Database\Seeders;

use App\Models\Configuration\Departamento;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Departamento::factory(10000)->create();
    }
}
