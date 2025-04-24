<?php

namespace App\Models\GuiaDevolucionAtributtes;

use App\Models\GuiaDevolucion;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Unidad;
use App\Models\Traits\AuditableTrait;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GuiaDevolucionDetail extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

    protected $table = "guia_devolucion_detail";
    protected $fillable = [
        'guia_devolucion_id',
        'producto_id',
        'unit_id',
        'cantidad',
        'lote_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function delete()
    {
        if (Auth::check()) {
            $this->deleted_by = Auth::id();
            $this->saveQuietly(); // Guarda sin disparar eventos innecesarios
        }

        return parent::delete();
    }

    public function GuiaDevolucion(){
        return $this->belongsTo(GuiaDevolucion::class, "guia_devolucion_id");
    }

    public function Producto(){
        return $this->belongsTo(Producto::class, "producto_id");
    }

    public function getUnit(){
        return $this->belongsTo(Unidad::class, "unit_id");
    }
}
