<?php

namespace App\Models\OrdenVentaAtributtes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentosTransporteOrdenVenta extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "documentacion_transp_ov";
    protected $fillable = [
        'transportes_ov_id',
        'comprobante_trasp_ov_id',
        'numero_documento',
        'monto',
        'n_cajas',
        'observacion',
    ];

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

    public function getTransporte(){
        return $this->belongsTo(TransportesOrdenVenta::class, "transportes_ov_id");
    }

    public function getComprobante(){
        return $this->belongsTo(ComprobanteTransporte::class, "transportes_ov_id");
    }
}
