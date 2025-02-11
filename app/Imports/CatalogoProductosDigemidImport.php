<?php

namespace App\Imports;

use App\Models\ProductoAtributtes\CatalogoProductosDigemid;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CatalogoProductosDigemidImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $cod_prod = trim(strval($row["cod_prod"]));
        $fraccion = trim(strval($row["fraccion"]));
        
        $catalogo = CatalogoProductosDigemid::create([
            "cod_prod" => $cod_prod,
            "nom_prod" => $row["nom_prod"] ?? null,
            "concent" => $row["concent"] ?? null,
            "nom_form_farm" => $row["nom_form_farm"] ?? null,
            "presentac" => $row["presentac"] ?? null,
            "fraccion" => $fraccion ?? null,
            "num_regsan" => $row["num_regsan"] ?? null,
            "nom_titular" => $row["nom_titular"] ?? null,
            "nom_fabricante" => $row["nom_fabricante"] ?? null,
            "nom_ifa" => $row["nom_ifa"] ?? null,
            "nom_rubro" => $row["nom_rubro"] ?? null,
            "situacion" => $row["situacion"] ?? null,
        ]);

        return $catalogo;
    }

    public function headingRow(): int
    {
        return 7;
    }

    public function rules(): array
    {
        return [
            '*.cod_prod' => ['required'],
            '*.nom_prod' => ['required'],
        ];
    }
}
