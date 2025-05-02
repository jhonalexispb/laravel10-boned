<?php

namespace App\Models\GuiasPrestamoAtributtes;

use App\Models\GuiaPrestamo;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use App\Models\ProductoAtributtes\Unidad;
use App\Models\Traits\AuditableTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GuiaPrestamoDetalle extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "guias_prestamo_detalle";
    protected $fillable = [
        'guia_prestamo_id',
        'producto_id',
        'unit_id',
        'lote_id',
        'cantidad',
        'stock',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function guia_prestamo(){
        return $this->belongsTo(GuiaPrestamo::class, "guia_prestamo_id");
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
