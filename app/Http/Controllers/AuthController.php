<?php

namespace App\Http\Controllers;

use App\Models\GuiaPrestamo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
 
 
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        //$this->authorize("create", User::class);
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
 
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
 
        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();
 
        return response()->json($user, 201);
    }
 
 
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
 
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
 
        return $this->respondWithToken($token);
    }
 
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }
 
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
 
        return response()->json(['message' => 'Successfully logged out']);
    }
 
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
 
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {   
        $user = auth("api")->user();
        $permissions = auth("api")->user()->getAllPermissions()->map(function($perm){
            return $perm -> name;
        });

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                "name" => $user->name,
                "gender" => $user->gender,
                "full_name" => $user->name . ' ' . $user->surname,
                "email" => $user->email,
                "avatar" => $user->avatar ? env("APP_URL") . "storage/" . $user->avatar : 'https://cdn-icons-png.flaticon.com/512/18269/18269639.png',
                "role_name" => $user->role->name ?? null,
                "permissions" => $permissions,
                "guia_prestamo" => $this->getUserPayload($user)
            ]
        ]);
    }

    protected function getUserPayload($user)
    {
        $guiaPendiente = GuiaPrestamo::where('user_encargado_id', $user->id)
            ->whereIn('state', [2, 3])
            ->latest('created_at')
            ->with('detalles.producto.get_laboratorio', 'detalles.lote')
            ->first();

        $detallesGuia = $guiaPendiente?->detalles;

        return [
            "codigo" => $guiaPendiente?->codigo,
            "productos_guia_prestamo" => $detallesGuia?->map(function ($p) {
                $producto = $p->producto;
                if (!$producto) return null;

                return [
                    "id" => $producto->id,
                    "sku" => $producto->sku,
                    "laboratorio" => $producto->get_laboratorio->name ?? '',
                    "laboratorio_id" => $producto->laboratorio_id,
                    "color_laboratorio" => $producto->get_laboratorio->color ?? '',
                    "nombre" => $producto->nombre,
                    "caracteristicas" => $producto->caracteristicas,
                    "nombre_completo" => $producto->nombre . ' ' . $producto->caracteristicas,
                    "pventa" => $producto->pventa ?? '0.0',
                    "stock" => $p->stock ?? '0',
                    "imagen" => $producto->imagen ?? env("IMAGE_DEFAULT"),
                    "lote" => $p->lote->lote ?? 'SIN LOTE',
                    "fecha_vencimiento" => ' FV: ' . $p->lote->fecha_vencimiento ? Carbon::parse($p->lote->fecha_vencimiento)->format("d-m-Y") : 'SIN FECHA DE VENCIMIENTO',
                    "maneja_escalas" => $producto->maneja_escalas && $producto->get_escalas->where('state', 1)->count() > 0,
                    "escalas" => $producto->maneja_escalas
                        ? $producto->get_escalas->where('state', 1)->map(fn($e) => [
                            "precio" => $e->precio,
                            "cantidad" => $e->cantidad,
                        ])->values()
                        : [],
                ];
            })->filter()->values() ?? collect(),
        ];
    }
}
