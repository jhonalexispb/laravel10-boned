<?php

namespace App\Models\ClienteSucursalAtributtes;

use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Dni extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = "dni_clientes";

    protected $fillable = [
        "numero",
        "nombre",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Celular");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function rucs()
    {
        return $this->hasManyThrough(
            Cliente::class,              // Modelo destino (Ruc)
            DniSucursal::class,  // Tabla intermedia (CelularSucursal)
            'dni_id',            // Clave foránea en la tabla intermedia (CelularSucursal)
            'id',                    // Clave primaria en la tabla de destino (Ruc)
            'id',                    // Clave primaria en la tabla de origen (Celular)
            'ruc_id'                 // Clave foránea en la tabla intermedia (CelularSucursal)
        );
    }

    public function getRucAsoc()
    {
        return $this->rucs()->first(); // Obtener el primer ruc asociado al celular
    }
}
