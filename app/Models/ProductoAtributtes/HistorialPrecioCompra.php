<?php

namespace App\Models\ProductoAtributtes;

use App\Models\Producto;
use App\Models\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HistorialPrecioCompra extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "producto_hist_p_compra";
    protected $fillable = [
        'producto_id',
        'precio',
        'created_by',
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }
}
