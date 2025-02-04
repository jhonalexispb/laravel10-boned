<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\configuration\Bank as ConfigurationBank;
use App\Models\configuration\ComprobantePago;
use App\Models\Configuration\RelacionBankComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class bankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $bank = ConfigurationBank::where("name","like","%".$search."%")
                                ->with('getComprobantes')
                                ->orderBy("id","desc")
                                ->paginate(25);
        return response()->json([
            "total" => $bank->total(),
            "bank" => $bank->map(function($b){
                return [
                    "id" => $b->id,
                    "name" => $b->name,
                    "image" => $b->image ? env("APP_URL")."storage/".$b->image : null,
                    "state" => $b->state,
                    "created_at" => $b->created_at->format("Y-m-d h:i A"),
                    "comprobantes" => $b->getComprobantes->map(function($comprobanteRel) {
                        return [
                            "id_relacion" => $comprobanteRel->id,
                            "tipo_caracter" => $comprobanteRel->tipo_caracter,
                            "ncaracteres" => $comprobanteRel->ncaracteres,
                            "ubicacion_codigo" => $comprobanteRel->ubicacion_codigo,
                            "img_ejemplo" => $comprobanteRel->img_ejemplo,
                            "state_relacion" => $comprobanteRel->state,
                            "created_at_relacion" => $comprobanteRel->created_at->format("Y-m-d h:i A"),
                            "comprobante" => [
                                "id" => $comprobanteRel->comprobante->id, // Aquí accedes a los datos del comprobante
                                "name" => $comprobanteRel->comprobante->name,
                            ]
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_exist_bank = ConfigurationBank::where("name", $request->name)->first();
        if($is_exist_bank){
            return response()->json([
                "message" => 403,
                "message_text" => "el nombre del banco ya existe"
            ],422);
        }

        if($request->hasFile("imagebank")){
            $path = Storage::putFile("bank",$request->file("imagebank"));
            $request->request->add(["image" => $path]);
        }

        $bank = ConfigurationBank::create($request->all());
        return response()->json([
            "message" => 200,
            "bank" => [
                "id" => $bank->id,
                "name" => $bank->name,
                "image" => $bank->image ? env("APP_URL")."storage/".$bank->image : null,
                "state" => $bank->state ?? 1,
                "created_at" => $bank->created_at->format('Y-m-d h:i A'),
                "comprobantes" => $bank->getComprobantes->map(function($comprobanteRel) {
                    return [
                        "id_relacion" => $comprobanteRel->id,
                        "tipo_caracter" => $comprobanteRel->tipo_caracter,
                        "ncaracteres" => $comprobanteRel->ncaracteres,
                        "ubicacion_codigo" => $comprobanteRel->ubicacion_codigo,
                        "img_ejemplo" => $comprobanteRel->img_ejemplo,
                        "state_relacion" => $comprobanteRel->state,
                        "created_at_relacion" => $comprobanteRel->created_at->format("Y-m-d h:i A"),
                        "comprobante" => [
                            "id" => $comprobanteRel->comprobante->id, // Aquí accedes a los datos del comprobante
                            "name" => $comprobanteRel->comprobante->name,
                        ]
                    ];
                })
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $is_exist_bank = ConfigurationBank::where("name", $request->name)
                                            ->where("id","<>",$id)
                                            ->first();
        if($is_exist_bank){
            return response()->json([
                "message" => 403,
                "message_text" => "el nombre del banco ya existe"
            ],422);
        }

        $b = ConfigurationBank::findOrFail($id);

        if($request->hasFile("imagebank")){
            if($b->image){
                Storage::delete($b->image);
            }
            $path = Storage::putFile("bank",$request->file("imagebank"));
            $request->request->add(["image" => $path]);
        }

        $b->update($request->all());
        return response()->json([
            "message" => 200,
            "bank" => [
                "id" => $b->id,
                "name" => $b->name,
                "image" => $b->image ? env("APP_URL")."storage/".$b->image : null,
                "state" => $b->state,
                "created_at" => $b->created_at->format('Y-m-d h:i A'),
                "comprobantes" => $b->getComprobantes->map(function($comprobanteRel) {
                    return [
                        "id_relacion" => $comprobanteRel->id,
                        "tipo_caracter" => $comprobanteRel->tipo_caracter,
                        "ncaracteres" => $comprobanteRel->ncaracteres,
                        "ubicacion_codigo" => $comprobanteRel->ubicacion_codigo,
                        "img_ejemplo" => $comprobanteRel->img_ejemplo,
                        "state_relacion" => $comprobanteRel->state,
                        "created_at_relacion" => $comprobanteRel->created_at->format("Y-m-d h:i A"),
                        "comprobante" => [
                            "id" => $comprobanteRel->comprobante->id, // Aquí accedes a los datos del comprobante
                            "name" => $comprobanteRel->comprobante->name,
                        ]
                    ];
                })
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bank = ConfigurationBank::findOrFail($id);
        $bank->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function obtenerComprobantes(){
        $comprobantes = ComprobantePago::where('state','1')
                        ->orderBy("id","desc")
                        ->get();
        return response() -> json([
            "comprobantes" => $comprobantes->map(function($comprobante) {
                return [
                    "id" => $comprobante->id,
                    "name" => $comprobante->name,
                ];
            })
        ]);
    }

    public function registrarBancoComprobante(Request $request){
        $request->validate([
            'id_banco' => 'required|exists:bank,id', // Validar que el banco exista
            'id_comprobante_pago' => 'required|exists:comprobante_pago,id', // Validar que el comprobante exista
            'tipo_caracter' => 'required|in:1,2',
            'ncaracteres' => 'required|integer',
            'ubicacion_codigo' => 'nullable|string',
            'img_ejemplo_relation' => 'nullable|image|max:2048', // Validar que sea una imagen
        ]);

        $existingRelation = RelacionBankComprobante::where('id_banco', $request->id_banco)
        ->where('id_comprobante_pago', $request->id_comprobante_pago)
        ->first();

        if ($existingRelation) {
            return response()->json(['error' => 'La relación ya existe.'], 422);
        }

        if($request->hasFile("img_ejemplo_relation")){
            $path = Storage::putFile("relation_bank_comprobante_example",$request->file("img_ejemplo_relation"));
            $request->request->add(["img_ejemplo" => $path]);
        }

        $relacion = RelacionBankComprobante::create($request->all());
    

        return response()->json([
            "relacionBancoComprobante" => [
                "id_relacion" => $relacion->id,
                "tipo_caracter" => $relacion->tipo_caracter,
                "ncaracteres" => $relacion->ncaracteres,
                "ubicacion_codigo" => $relacion->ubicacion_codigo,
                "img_ejemplo" => $relacion->img_ejemplo,
                "state_relacion" => $relacion->state ?? 1, 
                "created_at_relacion" => $relacion->created_at->format("Y-m-d h:i A"),
                "comprobante" => [
                    "id" => $relacion->comprobante->id, // Aquí accedes a los datos del comprobante
                    "name" => $relacion->comprobante->name,
                ]
            ]
        ]);
    }
}
