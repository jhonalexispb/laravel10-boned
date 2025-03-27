<?php

namespace App\Models\OrdenCompraAtributtes;

use App\Models\OrdenCompra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\AuditableTrait;

class OrdenCompraCuotas extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;

    protected $table = "ordenes_compra_cuotas";
    protected $fillable = [
        "orden_compra_id",
        "title",
        "amount",
        "saldo",
        "dias_reminder",
        "state",
        "start",
        "reminder",
        "notes",
        "numero_unico",
        "fecha_cancelado",
        "notificado",
        "intentos_envio", //numero de veces que envio la notificacion
        "created_by",
        "updated_by",
    ];

    public function getOrdenCompra(){
        return $this->belongsTo(OrdenCompra::class, "orden_compra_id");
    }
}
