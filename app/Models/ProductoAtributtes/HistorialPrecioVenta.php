<?php

namespace App\Models\ProductoAtributtes;

use App\Models\Producto;
use App\Models\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HistorialPrecioVenta extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "producto_hist_p_venta";
    protected $fillable = [
        'producto_id',
        'lote_id',
        'precio',
        'comentario',
        'created_by',
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }

    public function lote(){
        return $this->belongsTo(ProductoLotes::class, "lote_id");
    }
}
