<?php

namespace App\Models;

use App\Models\Configuration\Proveedor;
use App\Models\Configuration\TypeComprobanteSerie;
use App\Models\GuiaDevolucionAtributtes\GuiaDevolucionDetail;
use App\Models\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GuiaDevolucion extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "guia_devolucion";
    protected $fillable = [
        'type_comp_serie_id',
        'correlativo',
        'proveedor_id',
        'order_compra_id',
        'date_justificado',
        'descripcion',
        'state',
        'created_by',
        'updated_by'
    ];

    public function typeComprobanteSerie(){
        return $this->belongsTo(TypeComprobanteSerie::class, "type_comp_serie_id");
    }

    public function proveedor(){
        return $this->belongsTo(Proveedor::class, "proveedor_id");
    }

    public function order_compra(){
        return $this->belongsTo(OrdenCompra::class, "order_compra_id");
    }

    public function detalles()
    {
        return $this->hasMany(GuiaDevolucionDetail::class, 'guia_devolucion_id');
    }
}
