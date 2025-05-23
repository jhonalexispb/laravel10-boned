<?php

namespace Database\Seeders;

use App\Models\Configuration\Departamento;
use App\Models\Configuration\Distrito;
use App\Models\Configuration\Provincia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UbigeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departamentos = json_decode(File::get(database_path('data/ubigeo_peru_2016_departamentos.json')), true);
        $provincias = json_decode(File::get(database_path('data/ubigeo_peru_2016_provincias.json')), true);
        $distritos = json_decode(File::get(database_path('data/ubigeo_peru_2016_distritos.json')), true);

        // 1. Insertar departamentos
        foreach ($departamentos as $dep) {
            Departamento::create([
                'id' => $dep['id'],
                'name' => $dep['name'],
                'state' => 1,
                'image' => null,
            ]);
        }

        // 2. Insertar provincias
        foreach ($provincias as $prov) {
            Provincia::create([
                'id' => $prov['id'],
                'name' => $prov['name'],
                'iddepartamento' => $prov['department_id'],
                'state' => 1,
                'image' => null,
            ]);
        }

        // 3. Insertar distritos
        foreach ($distritos as $dist) {
            Distrito::create([
                'id' => $dist['id'],
                'name' => $dist['name'],
                'idprovincia' => $dist['province_id'],
                'state' => 1,
                'image' => null,
            ]);
        }
    }
}
