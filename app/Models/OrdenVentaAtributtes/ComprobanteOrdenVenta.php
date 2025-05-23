<?php

namespace App\Models\OrdenVentaAtributtes;

use App\Models\ClienteSucursalAtributtes\EstadoDigemid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ComprobanteOrdenVenta extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "orden_venta_type_comprobante";
    protected $fillable = [
        'codigo',
        'venta',
        'name',
        'state',
    ];

    public function estadosDigemid()
    {
        return $this->belongsToMany(
            EstadoDigemid::class,
            'comp_ov_estdig_relation',
            'comp_ov_id',
            'esta_dig_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
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
