<?php

namespace App\Models\ClienteSucursalAtributtes;

use App\Models\OrdenVentaAtributtes\ComprobanteOrdenVenta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EstadoDigemid extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = "estados_digemid";

    protected $fillable = [
        "nombre",
    ];

    public function comprobantesPermitidos()
    {
        return $this->belongsToMany(
            ComprobanteOrdenVenta::class,
            'comp_ov_estdig_relation',
            'esta_dig_id',
            'comp_ov_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Registro digemid");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
}
