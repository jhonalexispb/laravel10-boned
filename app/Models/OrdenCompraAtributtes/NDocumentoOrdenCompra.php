<?php

namespace App\Models\OrdenCompraAtributtes;

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
        "type_comprobante_compra_id",
        "serie",
        "n_documento",
        "importe",
        "igv",
        "total",
        "state",
        "created_by",
        "updated_by",
    ];

    public function getTipoComprobantePago(){
        return $this->belongsTo(TipoComprobantePagoCompra::class, "type_comprobante_compra_id");
    }
}