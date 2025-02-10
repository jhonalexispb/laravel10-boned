<?php

namespace App\Models\Configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Laboratorio extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'laboratorio';

    protected $fillable = [
        "codigo",
        "name",
        "image",
        "color",
        "margen_minimo",
        "state"
    ];
    
    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Laboratorio");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'laboratorio_proveedor', 'laboratorio_id', 'proveedor_id');
    }

    public static function generarCodigo()
    {
        // Obtener el último código del laboratorio
        $ultimoCodigo = self::orderByDesc('codigo')->first();

        if ($ultimoCodigo) {
            $numeroUltimoCodigo = (int) $ultimoCodigo->codigo;
            // Incrementar el número
            $nuevoCodigo = $numeroUltimoCodigo + 1;
        } else {
            // Si no hay registros, comenzamos con el código 'codigo_1'
            $nuevoCodigo = 101;
        }

        return $nuevoCodigo;
    }

    public function getProductImagenAttribute()
    {
        $link = null;
        if($this->image){
            if(str_contains($this->image,"https://") || str_contains($this->image,"http://")){
                $link = $this->image;
            }else{
                $link =  env('APP_URL').'storage/'.$this->image;
            }
        }
        return $link;
    }
}
