<?php

namespace App\Models\ProductoConfiguration;

use App\Models\Configuration\Warehouse;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Unidad;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductoAlmacenes extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "producto_warehouse_relation";
    protected $fillable = [
        "producto_id",
        "unit_id",
        "warehouse_id",
        "pventa",
        "stock",
        "state"
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Producto Almacen");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function getProductos(){
        return $this->belongsTo(Producto::class,"producto_id");
    }

    public function getUnit(){
        return $this->belongsTo(Unidad::class,"unit_id");
    }

    public function getWarehouse(){
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }
}
