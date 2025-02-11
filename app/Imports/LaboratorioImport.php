<?php

namespace App\Imports;

use App\Models\Configuration\Laboratorio;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LaboratorioImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   
        $laboratorio = Laboratorio::create([
            "codigo" => $row["codigo"],
            "name" => $row["name"],
            "image" => $row["image"] ?? null,
            "color" => $row["color"] ?? null,
            "margen_minimo" =>$row["margen_minimo"],
            "state" => $row["state"]
        ]);

        return $laboratorio;
    }

    public function rules(): array
    {
        return [
            '*.codigo' => ['required'],
            '*.name' => ['required'],
        ];
    }
}
