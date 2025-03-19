<?php

namespace App\Models;

use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\OrdenCompraCuotas;
use App\Models\OrdenCompraAtributtes\OrdenCompraDetails;
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
        "type_comprobante_compra_id",
        "forma_pago_id",
        "igv_state",
        "date_recepcion",
        "date_revision",
        "descripcion",
        "notificacion",
        "mensaje_notificacion",
        "importe",
        "igv",
        "total",
        "fecha_ingreso",
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

        // Buscar todas las órdenes con el prefijo actual
        $ultimosCodigos = self::where('codigo', 'LIKE', "$prefijo%")->pluck('codigo');

        $maxNumero = 0; // Inicializar el número máximo

        // Recorrer los códigos y extraer el número más alto
        foreach ($ultimosCodigos as $codigo) {
            $numero = intval(str_replace($prefijo, '', $codigo)); // Extrae el número
            if ($numero > $maxNumero) {
                $maxNumero = $numero;
            }
        }

        // Sumar 1 al número más alto encontrado
        $nuevoNumero = $maxNumero + 1;

        // Retornar el nuevo código
        return $prefijo . $nuevoNumero;
    }

    public function getProveedor(){
        return $this->belongsTo(Proveedor::class, "proveedor_id");
    }

    public function getLaboratorio(){
        return $this->belongsTo(Laboratorio::class, "laboratorio_id");
    }

    public function getTypeComprobante(){
        return $this->belongsTo(TipoComprobantePagoCompra::class, "type_comprobante_compra_id");
    }

    public function getFormaPago(){
        return $this->belongsTo(FormaPagoOrdenesCompra::class, "forma_pago_id");
    }

    public function getCuotas(){
        return $this->hasMany(OrdenCompraCuotas::class, "orden_compra_id");
    }

    public function detalles()
    {
        return $this->hasMany(OrdenCompraDetails::class, 'orden_compra_id');
    }
}
