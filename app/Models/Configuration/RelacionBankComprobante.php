<?php

namespace App\Models\Configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelacionBankComprobante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'relacion_bank_comprobante';

    protected $fillable = [
        'id_banco',
        'id_comprobante_pago',
        'tipo_caracter',
        'ncaracteres',
        'ubicacion_codigo',
        'img_ejemplo',
        'state'
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function banco()
    {
        return $this->belongsTo(Bank::class, 'id_banco');
    }

    public function comprobante()
    {
        return $this->belongsTo(ComprobantePago::class, 'id_comprobante_pago');
    }
}
