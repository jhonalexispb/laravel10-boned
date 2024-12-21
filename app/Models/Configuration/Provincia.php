<?php

namespace App\Models\Configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provincia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provincia';

    protected $fillable = [
        "name",
        "image",
        "state",
        "iddepartamento",
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'iddepartamento');
    }

    public function distrito()
    {
        return $this->hasMany(Distrito::class, 'idprovincia');
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
