<?php

namespace App\Models\ProductoAtributtes;

use App\Models\Configuration\CategoriaProducto;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductoCategoria extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "producto_categoria_relation";
    protected $fillable = [
        "producto_id",
        "categoria_id",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Unidades");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function product(){
        return $this->belongsTo(Producto::class,"producto_id");
    }

    public function categoria(){
        return $this->belongsTo(CategoriaProducto::class,"categoria_id");
    }
}
