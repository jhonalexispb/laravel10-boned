<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraAtributtes\NDocumentoOrdenCompra;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Afectacion_igv;
use App\Models\ProductoAtributtes\ProductoLotes;
use App\Models\ProductoAtributtes\Unidad;
use App\Models\Traits\AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCompraDetailsGestionado extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

    protected $table = "order_compra_detail_gestionado";
    protected $fillable = [
        "orden_compra_id",
        "oc_n_comprob_id",
        "prod_lote_rel_id",
        "afectacion_id",
        "unit_id",
        "producto_id",
        "cantidad",
        "total",
        "bonificacion",
        "comentario",
        "pcompra",
        "created_by",
        "updated_by",
    ];

    public function order_compra(){
        return $this->belongsTo(OrdenCompra::class, "orden_compra_id");
    }

    public function comprobante(){
        return $this->belongsTo(NDocumentoOrdenCompra::class, "oc_n_comprob_id");
    }

    public function afectacion(){
        return $this->belongsTo(Afectacion_igv::class, "afectacion_id");
    }

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }

    public function unit(){
        return $this->belongsTo(Unidad::class, "unit_id");
    }

    public function lote(){
        return $this->belongsTo(ProductoLotes::class, "prod_lote_rel_id");
    }
}
