<?php

namespace App\Models;

use App\Models\Configuration\CategoriaProducto;
use App\Models\Configuration\FabricanteProducto;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\LineaFarmaceutica;
use App\Models\Configuration\PrincipioActivo;
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

    protected $table = "productos";
    protected $fillable = [
        "sku",
        "tproducto",
        "codigobarra",
        "unidad_id",
        "laboratorio_id",
        "nombre",
        "caracteristicas",
        "categoria_id",
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
        "maneja_escalas",
        "maneja_lotes",
        "promocionable",
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

    public function scopeFilterAdvance($query, $producto_id, $laboratorio_id)
    {
        // Filtro por ID de producto
        if ($producto_id) {
            $query->where('id', '=', $producto_id);
        }

        // Filtro por ID de laboratorio
        if ($laboratorio_id) {
            $query->where('laboratorio_id', '=', $laboratorio_id);
        }

        return $query;
    }
    
    public function get_unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id'); // Unidad es el modelo relacionado
    }

    public function get_laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id'); // Laboratorio es el modelo relacionado
    }

    public function get_lineaFarmaceutica()
    {
        return $this->belongsTo(LineaFarmaceutica::class, 'linea_farmaceutica_id'); // LíneaFarmaceutica es el modelo relacionado
    }

    public function get_fabricante()
    {
        return $this->belongsTo(FabricanteProducto::class, 'fabricante_id'); // Fabricante es el modelo relacionado
    }

    public function get_condicion_almacenamiento()
    {
        return $this->belongsToMany(CondicionAlmacenamiento::class,'producto_cond_almac_relation','producto_id','cond_almac_id');
    }

    public function get_principios_activos()
    {
        return $this->belongsToMany(PrincipioActivo::class,'producto_principio_relation','producto_id','principio_id');
    }

    public static function generarCodigo($laboratorioId)
    {
        $laboratorio = Laboratorio::find($laboratorioId);
        if ($laboratorio) {
            $codigoLaboratorio = $laboratorio->codigo; // Obtener el código del laboratorio

            $ultimoProducto = self::where('laboratorio_id', $laboratorioId)
                                  ->orderByDesc('sku')
                                  ->first();

            if ($ultimoProducto) {
                // Asegúrate de que el código tenga al menos la longitud esperada
                $numeroUltimoProducto = substr($ultimoProducto->sku, -5);

                if ($numeroUltimoProducto === 99999) {
                    return null; 
                }

                // Generar el nuevo código sumando 1 al número final del código
                $nuevoCodigo = $codigoLaboratorio . str_pad($numeroUltimoProducto + 1, 5, '0', STR_PAD_LEFT);
            } else {
                // Si no hay productos, comenzamos con el primer producto de ese laboratorio
                $nuevoCodigo = $codigoLaboratorio . '00001';
            }

            return $nuevoCodigo;
        }

        // Si no se encuentra el laboratorio, devolver null o algún valor por defecto
        return null;
    }









    public function get_categoria()
    {
        return $this->belongsToMany(CategoriaProducto::class,'producto_categoria_relation','producto_id','categoria_id');
    }

    public function get_lotes()
    {
        return $this->hasMany(ProductoLotes::class,'producto_id');
    }
}
