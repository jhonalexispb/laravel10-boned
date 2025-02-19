<?php

namespace App\Models;

use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\TipoComprobantePagoCompra;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrdenCompra extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "ordenes_compra";
    protected $fillable = [
        "codigo",
        "proveedor_id",
        "laboratorio_id",
        "type_comprobante_compra_id",
        "forma_pago_id",
        "igv_state",
        "date_recepcion",
        "date_revision",
        "descripcion",
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

    public static function generarCodigo()
    {
        $año = date('Y'); // Año actual
        $prefijo = "OC-$año-";

        // Buscar la última orden con el mismo año
        $ultimaOrden = self::where('codigo', 'LIKE', "$prefijo%")
                           ->orderBy('codigo', 'desc')
                           ->first();

        // Si hay una orden previa, extraemos el número y sumamos 1
        if ($ultimaOrden) {
            $ultimoNumero = intval(substr($ultimaOrden->codigo, strlen($prefijo))) + 1;
        } else {
            $ultimoNumero = 1;
        }

        // Retornar el nuevo código
        return $prefijo . $ultimoNumero;
    }

    public function getProveedor(){
        return $this->belongsTo(Proveedor::class, "proveedor_id");
    }

    public function getLaboratorio(){
        return $this->belongsTo(Laboratorio::class, "laboratorio_id");
    }

    public function getTypeComporbante(){
        return $this->belongsTo(TipoComprobantePagoCompra::class, "type_comprobante_compra_id");
    }

    public function getFormaPago(){
        return $this->belongsTo(FormaPagoOrdenesCompra::class, "forma_pago_id");
    }
}
