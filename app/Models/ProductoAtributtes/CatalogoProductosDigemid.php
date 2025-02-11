<?php

namespace App\Models\ProductoAtributtes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoProductosDigemid extends Model
{
    use HasFactory;

    protected $table = "catalogo_productos_farmaceuticos_digemid";
    protected $fillable = [
        "cod_prod",
        "nom_prod",
        "concent",
        "nom_form_farm",
        "presentac",
        "fraccion",
        "num_regsan",
        "nom_titular",
        "nom_fabricante",
        "nom_ifa",
        "nom_rubro",
        "situacion",
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
}
