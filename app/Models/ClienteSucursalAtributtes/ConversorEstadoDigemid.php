<?php

namespace App\Models\ClienteSucursalAtributtes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversorEstadoDigemid extends Model
{
    use HasFactory;
    protected $table = "conversion_estados_digemid";

    public function getConversionsByEstado($estadoIds)
    {
        return $this->whereIn('estado_digemid_id', $estadoIds)->get();
    }

    public function estadoDigemid()
    {
        return $this->belongsTo(EstadoDigemid::class, 'transform_estado_digemid_id');
    }
}
