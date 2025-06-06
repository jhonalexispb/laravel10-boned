<?php

namespace App\Models;

use App\Models\ClienteSucursalAtributtes\ModoFacturacion;
use App\Models\configuration\lugarEntrega;
use App\Models\OrdenVentaAtributtes\ComprobanteOrdenVenta;
use App\Models\OrdenVentaAtributtes\DocumentosTransporteOrdenVenta;
use App\Models\OrdenVentaAtributtes\OrdenVentaDetalle;
use App\Models\OrdenVentaAtributtes\TransportesOrdenVenta;
use App\Models\Traits\AuditableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\LogOptions;

class OrdenVenta extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

    protected $table = "orden_venta";
    protected $fillable = [
        "codigo",
        "cliente_id",
        "comprobante_id",
        "total",
        "forma_pago",
        "comentario",
        "zona_reparto",
        "transporte_id",
        "state_orden",
        "fecha_envio",
        "fecha_creacion_comprobante",
        "estado_pago",
        "monto_pagado",
        "state_fisico",
        "fecha_empaquetado",
        "fecha_cargado",
        "fecha_agencia",
        "fecha_entregado_cliente",

        "state_seguimiento",
        "fecha_corroboracion",
        "documento_transporte_id",
        "guia_prestamo_id",
        "modo_entrega",
        "lugar_entrega_id",
        "created_by",
        "updated_by",
        "deleted_by",
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

    public static function generarCodigo($usuario_id)
    {
        $year = date('Y'); // Año actual
        $prefijo = "OV-$usuario_id-$year-";

        // Buscar la última orden con el mismo año ordenando por el número final correctamente
        $ultimaOrden = self::withTrashed()
                        ->where('codigo', 'LIKE', "$prefijo%")
                        ->orderByRaw("CAST(SUBSTRING(codigo, LENGTH(?) + 1) AS UNSIGNED) DESC", [$prefijo])
                        ->first();

        if ($ultimaOrden) {
            $ultimoNumero = intval(substr($ultimaOrden->codigo, strlen($prefijo))) + 1;
        } else {
            $ultimoNumero = 1;
        }

        // Retornar el nuevo código
        return $prefijo . $ultimoNumero;
    }

    public function cliente(){
        return $this->belongsTo(ClientesSucursales::class, "cliente_id");
    }

    public function comprobante(){
        return $this->belongsTo(ComprobanteOrdenVenta::class, "comprobante_id");
    }

    public function transporte(){
        return $this->belongsTo(TransportesOrdenVenta::class, "transporte_id");
    }

    public function documento_transporte(){
        return $this->belongsTo(DocumentosTransporteOrdenVenta::class, "documento_transporte_id");
    }

    public function creador(){
        return $this->belongsTo(User::class, "created_by");
    }

    public function detalles()
    {
        return $this->hasMany(OrdenVentaDetalle::class, 'order_venta_id');
    }

    public function guia_prestamo()
    {
        return $this->belongsTo(GuiaPrestamo::class, "guia_prestamo_id");
    }

    public function lugar_entrega()
    {
        return $this->belongsTo(lugarEntrega::class, "lugar_entrega_id");
    }
}