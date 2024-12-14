<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\configuration\MethodPayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class methodPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $method_payment = MethodPayment::where("name", "like", "%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $method_payment->total(),
            "methodPayment" => $method_payment->map(function($method){
                return [
                    "id" => $method->id,
                    "name" => $method->name,
                    "image" => $method->image ? env("APP_URL")."storage/".$method->image : null,
                    "state" => $method->state,
                    "created_at" => $method->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_exist_method = MethodPayment::where("nombre", $request->name)->first();
        if($is_exist_method){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre del método ya existe"
            ]);
        }

        if($request->hasFile("image")){
            $path = Storage::putFile("methods_payents",$request->file("image"));
            $request->request->add(["image" => $path]);
        }

        $method_payment = MethodPayment::create($request->all());
        return response()->json([
            "message" => 200,
            "method_payment" => [
                "id" => $method_payment->id,
                "name" => $method_payment->name,
                "image" => $method_payment->image ? env("APP_URL")."storage/".$method_payment->image : null,
                "state" => $method_payment->state,
                "created_at" => $method_payment->created_at->format('Y-m-d h:i A'),
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
        $is_exist_method = MethodPayment::where("name", $request->name)
                                            ->where("id","<>",$id)
                                            ->first();
        if($is_exist_method){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre del método ya existe"
            ]);
        }

        $method = MethodPayment::findOrFail($id);

        if($request->hasFile("image")){
            if($method->image){
                Storage::delete($method->image);
            }
            $path = Storage::putFile("methods_payents",$request->file("image"));
            $request->request->add(["image" => $path]);
        }

        $method->update($request->all());
        return response()->json([
            "message" => 200,
            "method_payment" => [
                "id" => $method->id,
                "name" => $method->name,
                "image" => $method->image ? env("APP_URL")."storage/".$method->image : null,
                "state" => $method->state,
                "created_at" => $method->created_at->format('Y-m-d h:i A'),
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $method = MethodPayment::findOrFail($id);
        if($method->image){
            Storage::delete($method->image);
        }

        $method->delete();

        return response()->json([
            "message" => 200
        ]);
    }
}