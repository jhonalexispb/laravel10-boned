<?php

namespace App\Models;

use App\Models\GuiasPrestamoAtributtes\GuiaPrestamoDetalle;
use App\Models\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GuiaPrestamo extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, AuditableTrait;
    protected $table = "guias_prestamo";
    protected $fillable = [
        'codigo',
        'user_encargado_id',
        'comentario',
        'fecha_entrega',
        'fecha_gestionado',
        'fecha_revisado',
        'created_by',
        'updated_by',
        'state',
    ];

    public static function generar_codigo()
    {
        $año = date('Y'); // Año actual
        $prefijo = "GP-$año-";

        // Buscar todas las órdenes con el prefijo actual
        $ultimosCodigos = self::withTrashed()
        ->where('codigo', 'LIKE', "$prefijo%")
        ->pluck('codigo');

        $maxNumero = 0; // Inicializar el número máximo

        // Recorrer los códigos y extraer el número más alto
        foreach ($ultimosCodigos as $codigo) {
            $numero = intval(str_replace($prefijo, '', $codigo)); // Extrae el número
            if ($numero > $maxNumero) {
                $maxNumero = $numero;
            }
        }

        // Sumar 1 al número más alto encontrado
        $nuevoNumero = $maxNumero + 1;

        // Retornar el nuevo código
        return $prefijo . $nuevoNumero;
    }

    public function user_encargado(){
        return $this->belongsTo(User::class, "user_encargado_id");
    }

    public function creador(){
        return $this->belongsTo(User::class, "created_by");
    }

    public function detalles()
    {
        return $this->hasMany(GuiaPrestamoDetalle::class, 'guia_prestamo_id');
    }

    public function actualizarEstadoPorDetalles()
    {
        $tieneMovimientos = $this->detalles()->exists(); // Verifica si hay detalles relacionados

        $this->state = $tieneMovimientos ? 1 : 0; // 1: Pendiente, 0: En creación
        $this->save();
    }


    public function puedeCambiarAEstado($nuevoEstado): ?string {
        if ($nuevoEstado == 2) {
            if (!$this->user_encargado_id) return "La guía no tiene un encargado asignado.";
            if ($this->detalles->isEmpty()) return "La guía no tiene productos registrados.";
        }

        if ($nuevoEstado == 1 && $this->state != 2) {
            return "La guía no tiene el estado de entregado.";
        }

        return null;
    }
}
