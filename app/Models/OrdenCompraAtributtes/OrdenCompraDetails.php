<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Unidad;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrdenCompraDetails extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "ordenes_compra_detail";
    protected $fillable = [
        "orden_compra_id",
        "n_comprobante_id",
        "producto_id",
        "unit_id",
        "cantidad",
        "p_compra",
        "total",
        "margen_ganancia",
        "p_venta",
        "condicion_vencimiento",
        "fecha_vencimiento",
        "bonificacion",
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

    public function getOrdenCompra(){
        return $this->belongsTo(OrdenCompra::class, "orden_compra_id");
    }

    public function getNDocumento(){
        return $this->belongsTo(NDocumentoOrdenCompra::class, "n_comprobante_id");
    }

    public function getProducto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }

    public function getUnit(){
        return $this->belongsTo(Unidad::class, "unit_id");
    }
}
