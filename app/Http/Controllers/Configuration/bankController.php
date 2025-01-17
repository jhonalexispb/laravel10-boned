<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\configuration\Bank as ConfigurationBank;
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

        $bank = ConfigurationBank::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $bank->total(),
            "bank" => $bank->map(function($b){
                return [
                    "id" => $b->id,
                    "name" => $b->name,
                    "image" => $b->image ? env("APP_URL")."storage/".$b->image : null,
                    "state" => $b->state,
                    "created_at" => $b->created_at->format("Y-m-d h:i A")
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
}
