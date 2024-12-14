<?php

namespace App\Models\configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class lugarEntrega extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'lugares_de_entrega';

    protected $fillable = [
        "name",
        "address",
        "state",
        "destination_coordinates",
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
