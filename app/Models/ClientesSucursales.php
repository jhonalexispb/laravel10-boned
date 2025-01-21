<?php

namespace App\Models;

use App\Models\ClienteSucursalAtributtes\CelularSucursal;
use App\Models\ClienteSucursalAtributtes\CorreoSucursal;
use App\Models\ClienteSucursalAtributtes\DniSucursal;
use App\Models\ClienteSucursalAtributtes\SucursalesActivas;
use App\Models\ClienteSucursalAtributtes\SucursalesCierreDefinitivo;
use App\Models\ClienteSucursalAtributtes\SucursalesCierreTemporal;
use App\Models\ClienteSucursalAtributtes\SucursalesPersonaNatural;
use App\Models\ClienteSucursalAtributtes\SucursalesSinRegistroDigemid;
use App\Models\Configuration\Distrito;
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
        "celular",
        "correo",
        "ubicacion",
        "deuda",
        "linea_credito",
        "modo_trabajo",
        "categoria_digemid_id",
        "state",
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

    public function ruc()
    {
        return $this->belongsTo(Cliente::class, 'ruc_id');
    }

    public function distrito()
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
                    ->with('celular');;
    }

    public function getCorreo()
    {
        return $this->hasMany(CorreoSucursal::class, 'cliente_sucursal_id')
                    ->with('correo');;
    }

    public function getDni()
    {
        return $this->hasMany(DniSucursal::class, 'cliente_sucursal_id')
                    ->with('dni');
    }

    public function getInformacionPorEstadoDigemid()
    {
        switch ($this->estado_digemid) {
            case 1: // Activos
                return $this->hasOne(SucursalesActivas::class, 'cliente_sucursal_id');
            case 2: // Cierre Temporal
                return $this->hasOne(SucursalesCierreTemporal::class, 'cliente_sucursal_id');
            case 3: // Cierre Definitivo
                return $this->hasOne(SucursalesCierreDefinitivo::class, 'cliente_sucursal_id');
            case 4: // Sin Registro Digemid
                return $this->hasOne(SucursalesSinRegistroDigemid::class, 'cliente_sucursal_id');
            case 5: // Persona Natural
                return $this->hasOne(SucursalesPersonaNatural::class, 'cliente_sucursal_id');
            default:
                return null; // O puedes devolver un valor por defecto si no hay coincidencia
        }
    }
}
