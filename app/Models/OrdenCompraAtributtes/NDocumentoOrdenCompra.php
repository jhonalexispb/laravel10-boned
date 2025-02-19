<?php

namespace App\Models\OrdenCompraAtributtes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NDocumentoOrdenCompra extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "ordenes_compra_n_comprobantes";
    protected $fillable = [
        "type_comprobante_compra_id",
        "serie",
        "n_documento",
        "importe",
        "igv",
        "total",
        "state",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Condicion almacenamiento");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function getTipoComprobantePago(){
        return $this->belongsTo(TipoComprobantePagoCompra::class, "type_comprobante_compra_id");
    }
}