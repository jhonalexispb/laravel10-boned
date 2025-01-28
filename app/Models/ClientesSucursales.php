<?php

namespace App\Models;

use App\Models\ClienteSucursalAtributtes\CelularSucursal;
use App\Models\ClienteSucursalAtributtes\ConversorEstadoDigemid;
use App\Models\ClienteSucursalAtributtes\CorreoSucursal;
use App\Models\ClienteSucursalAtributtes\DniSucursal;
use App\Models\ClienteSucursalAtributtes\EstadoDigemid;
use App\Models\ClienteSucursalAtributtes\RegistroDigemid;
use App\Models\Configuration\Distrito;
use App\Models\configuration\lugarEntrega;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ClientesSucursales extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = "cliente_sucursales";

    protected $fillable = [
        "ruc_id",
        "nombre_comercial",
        "direccion",
        "distrito",
        "deuda",
        "linea_credito",
        "modo_trabajo",
        "categoria_digemid_id",
        "estado_digemid",
        "image",
        "image_public_id",
        "nregistro_id",
        "documento_en_proceso",
        "documento_en_proceso_public_id",
        "state",
        "modo_facturacion_id",
        "dias",
        "formaPago"
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} ClienteSucursal");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public static function getOrCreateCliente($ruc, $razon_social)
    {
        return self::withTrashed()->where('ruc', $ruc)->firstOrCreate(
            ['ruc' => $ruc],
            ['razonSocial' => $razon_social]
        );
    }

    public function ruc()
    {
        return $this->belongsTo(Cliente::class, 'ruc_id');
    }

    public function getNameDistrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito');
    }

    public function categoriaDigemid()
    {
        return $this->belongsTo(CategoriaDigemid::class, 'categoria_digemid_id');
    }

    public function getCelular()
    {
        return $this->hasMany(CelularSucursal::class, 'cliente_sucursal_id')
                    ->with('getNumberCelular');
    }

    public function getCorreo()
    {
        return $this->hasMany(CorreoSucursal::class, 'cliente_sucursal_id')
                    ->with('correo');
    }

    public function getDni()
    {
        return $this->hasOne(DniSucursal::class, 'cliente_sucursal_id')
        ->with('dni');
    }

    public function getRegistro()
    {
        return $this->belongsTo(RegistroDigemid::class, 'nregistro_id');
    }

    public function getDirecciones()
    {
        return $this->hasMany(lugarEntrega::class, 'sucursal_id');
    }

    public function getEstadoDigemid()
    {
        return $this->belongsTo(EstadoDigemid::class, 'estado_digemid');
    }
}
