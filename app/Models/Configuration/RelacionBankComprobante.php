<?php

namespace App\Models\Configuration;

use App\Models\ComprobantePago;
use App\Models\configuration\Bank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelacionBankComprobante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'relacion_bank_comprobante';

    protected $fillable = [
        'idBanco',
        'idComprobantePago',
        'tipoCaracter',
        'ncaracteres',
        'nombre',
        'ubicacionCodigo',
        'imgEjemplo',
        'state'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'idBanco');
    }

    public function comprobantePago()
    {
        return $this->belongsTo(ComprobantePago::class, 'idComprobantePago');
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
}
