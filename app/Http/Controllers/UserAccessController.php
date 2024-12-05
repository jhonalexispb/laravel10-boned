<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $user = User::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);

        return response()->json([
            "total" => $user->total(),
            "users" => $user->map(function($user){
                return[
                    "id" => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'surname' => $user->surname,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id,
                    'role' => $user->role,
                    'roles' => $user->roles,
                    'sucursal_id' => $user->sucursal_id,
                    'gender' => $user->gender,
                    'avatar' => $user->avatar ? env("APP_URL")."storage/".$user->avatar : NULL,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $USER_EXIST = User::where('email',$request->email)->first();
        if($USER_EXIST){
            return response() -> json([
                "message" => 403,
                "message_text" => "El usuario ya existe"
            ]);
        }

        if($request->hasFile("imagen")){
            $path = Storage::putFile("users",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        if($request->password){
            $request->request->add(["password" => bcrypt($request->password)]);
        }

        $role = Role::findOrFail($request->role_id);
        $user = User::create(  $request->all());
        $user->assignRole($role);
        return response()->json([
            "message" => 200,
            "user" => [
                "id" => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'surname' => $user->surname,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'roles' => $user->roles,
                'sucursal_id' => $user->sucursal_id,
                'gender' => $user->gender,
                'avatar' => $user->avatar ? env("APP_URL")."storage/".$user->avatar : NULL,
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
        $USER_EXIST = User::where('email',$request->email)
                        ->where("id","<>",$id)
                        ->first();
        if($USER_EXIST){
            return response() -> json([
                "message" => 403,
                "message_text" => "El usuario ya existe"
            ]);
        }

        $user = User::findOrFail($id);

        if($request->hasFile("imagen")){
            if($user->avatar){
                Storage::delete($user->avatar);
            }
            $path = Storage::putFile("users",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        if($request->password){
            $request->request->add(["password" => bcrypt($request->password)]);
        }

        if($request->role_id != $user->role_id){
            //viejo rol
            $role_old = Role::findOrFail($user->role_id);
            $user->removeRole($role_old);

            //Nuevo rol
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);   
        }

        $user->update($request->all());

        return response()->json([
            "message" => 200,
            "user" => [
                "id" => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'surname' => $user->surname,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'roles' => $user->roles,
                'sucursal_id' => $user->sucursal_id,
                'gender' => $user->gender,
                'avatar' => $user->avatar ? env("APP_URL")."storage/".$user->avatar : NULL,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if($user->avatar){
            Storage::delete($user->avatar);
        }

        return response()->json([
            "message" => 200
        ]);
    }
}
