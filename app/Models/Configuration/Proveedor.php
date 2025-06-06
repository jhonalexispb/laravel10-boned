<?php

namespace App\Models\Configuration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'proveedor';

    protected $fillable = [
        "ruc",
        "razonSocial",
        "name",
        "address",
        "iddistrito",
        "email",
        "state",
        "idrepresentante",
    ];
    
    public function getActivitylogOptions(): LogOptions
    {
        // Aquí defines cómo se registrarán las actividades
        return LogOptions::defaults()
            ->logAll()  // Si deseas registrar todos los cambios
            ->logOnlyDirty()  // Opción de solo registrar cambios realizados (no todos los atributos)
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} Proveedor");
    }

    public function setCreatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function ubicacion(){
        return $this->belongsTo(Distrito::class, 'iddistrito');
    }

    public function representante(){
        return $this->belongsTo(RepresentanteProveedor::class, 'idrepresentante');
    }

    public function proveedorLaboratorios()
    {
        return $this->hasMany(ProveedorLaboratorio::class, 'proveedor_id');
    }

}
