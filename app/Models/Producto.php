<?php

namespace App\Models;

use App\Models\Configuration\CategoriaProducto;
use App\Models\Configuration\FabricanteProducto;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\LineaFarmaceutica;
use App\Models\ProductoAtributtes\CondicionAlmacenamiento;
use App\Models\ProductoAtributtes\ProductoLotes;
use App\Models\ProductoAtributtes\Unidad;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Producto extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "condicion_almacenamiento0";
    protected $fillable = [
        "sku",
        "tproducto",
        "codigobarra",
        "unidad_id",
        "laboratorio_id",
        "nombre",
        "descripcion",
        "registro_sanitario",
        "pventa",
        "pcompra",
        "stock",
        "stock_seguridad",
        "imagen",
        "linea_farmaceutica_id",
        "fabricante_id",
        "sale_boleta",
        "state",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Productos");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
    
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id'); // Unidad es el modelo relacionado
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id'); // Laboratorio es el modelo relacionado
    }

    public function lineaFarmaceutica()
    {
        return $this->belongsTo(LineaFarmaceutica::class, 'linea_farmaceutica_id'); // LíneaFarmaceutica es el modelo relacionado
    }

    public function fabricante()
    {
        return $this->belongsTo(FabricanteProducto::class, 'fabricante_id'); // Fabricante es el modelo relacionado
    }

    public function categoria()
    {
        return $this->belongsToMany(CategoriaProducto::class,'producto_categoria_relation','producto_id','categoria_id');
    }

    public function condicion_almacenamiento()
    {
        return $this->belongsToMany(CondicionAlmacenamiento::class,'producto_cond_almac_relation','producto_id','cond_almac_id');
    }

    public function lotes()
    {
        return $this->hasMany(ProductoLotes::class,'producto_id');
    }
}
