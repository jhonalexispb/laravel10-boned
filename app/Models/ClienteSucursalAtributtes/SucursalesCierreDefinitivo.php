<?php

namespace App\Models\ClienteSucursalAtributtes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SucursalesCierreDefinitivo extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = "sucursales_cierre_definitivo";

    protected $fillable = [
        "nregistro_id",
        "cliente_sucursal_id"
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} SucursalCierreDefinitivo");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function nregistro()
    {
        return $this->belongsTo(RegistroDigemid::class, 'nregistro_id');
    }
}
