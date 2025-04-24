<?php

namespace App\Models\Configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeComprobante extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "type_comprobante";
    protected $fillable = [
        "codigo",
        "nombre",
        "state",
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function serie(){
        return $this->hasMany(TypeComprobanteSerie::class);
    }
}
