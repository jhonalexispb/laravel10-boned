<?php

namespace App\Http\Controllers;

use App\Imports\CatalogoProductosDigemidImport;
use App\Models\ProductoAtributtes\CatalogoProductosDigemid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductoCatalogoDigemid extends Controller
{
    public function import_catalogo_digemid(Request $request){
        /* $request->validate([
            "import_file" => 'required|file|mimes:xls,xlsx,csv'
        ]) */;

        CatalogoProductosDigemid::truncate();

        DB::statement('ALTER TABLE catalogo_productos_farmaceuticos_digemid AUTO_INCREMENT = 1');

        $path = $request->file("import_file");
        $data = Excel::import(new CatalogoProductosDigemidImport,$path);

        return response()->json([
            "message" => 200
        ]);
    }

    public function getCodigoDigemid(Request $request)
    {   
        $registro_sanitario = $request->registro_sanitario;

        $codigos = CatalogoProductosDigemid::where('num_regsan',$registro_sanitario)
                        ->orderBy('id', 'desc')
                        ->get();
                                
        return response()->json([
            'codigos' => $codigos->map(function ($l) {
                return [
                    "cod_prod" => $l->cod_prod,
                    "nom_prod" => $l->nom_prod,
                    "concent" => $l->concent,
                    "nom_form_farm" => $l->nom_form_farm,
                    "presentac" => $l->presentac,
                    "fraccion" => $l->fraccion,
                    "num_regsan" => $l->num_regsan,
                    "nom_titular" => $l->nom_titular,
                    "nom_fabricante" => $l->nom_fabricante,
                    "nom_ifa" => $l->nom_ifa,
                    "nom_rubro" => $l->nom_rubro,
                    "situacion" => $l->situacion,
                ];
            }),
        ]);
    }
}
