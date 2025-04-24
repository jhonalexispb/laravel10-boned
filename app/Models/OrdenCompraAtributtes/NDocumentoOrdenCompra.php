<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\AuditableTrait;

class NDocumentoOrdenCompra extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

    protected $table = "ordenes_compra_n_comprobantes";
    protected $fillable = [
        "orden_compra_id",
        "type_comprobante_compra_id",
        "serie",
        "n_documento",
        "modo_pago",
        "igv_state",
        "importe",
        "igv",
        "total",
        "state",
        "fecha_emision",
        "comentario",
        "created_by",
        "updated_by",
    ];

    public function getTipoComprobantePago(){
        return $this->belongsTo(TipoComprobantePagoCompra::class, "type_comprobante_compra_id");
    }

    public function order_compra(){
        return $this->belongsTo(OrdenCompra::class, "orden_compra_id");
    }
}