<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrdenCompraCuotas extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "ordenes_compra_cuotas";
    protected $fillable = [
        "orden_compra_id",
        "title",
        "amount",
        "saldo",
        "dias_reminder",
        "state",
        "start",
        "reminder",
        "notes",
        "numero_unico",
        "fecha_cancelado",
        "notificado",
        "intentos_envio", //numero de veces que envio la notificacion
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Orden cuota cuotas");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function getOrdenCompra(){
        return $this->belongsTo(OrdenCompra::class, "orden_compra_id");
    }
}
