<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Unidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\AuditableTrait;
use Illuminate\Support\Facades\Auth;

class OrdenCompraDetails extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

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
        "created_by",
        "updated_by",
        "deleted_by"
    ];

    public function delete()
    {
        if (Auth::check()) {
            $this->deleted_by = Auth::id();
            $this->saveQuietly(); // Guarda sin disparar eventos innecesarios
        }

        return parent::delete();
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
