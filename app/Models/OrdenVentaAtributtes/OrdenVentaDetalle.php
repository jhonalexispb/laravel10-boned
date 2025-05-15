<?php

namespace App\Models\OrdenVentaAtributtes;

use App\Models\OrdenVenta;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use App\Models\ProductoAtributtes\Unidad;
use App\Models\Traits\AuditableTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenVentaDetalle extends Model
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "orden_venta_detalle";
    protected $fillable = [
        'order_venta_id',
        'producto_id',
        'unit_id',
        'lote_id',
        'cantidad',
        'pventa',
        'total',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function orden_venta(){
        return $this->belongsTo(OrdenVenta::class, "order_venta_id");
    }

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }

    public function unidad(){
        return $this->belongsTo(Unidad::class, "unit_id");
    }

    public function lote(){
        return $this->belongsTo(ProductoLotes::class, "lote_id");
    }

    public function creador(){
        return $this->belongsTo(User::class, "created_by");
    }
}
