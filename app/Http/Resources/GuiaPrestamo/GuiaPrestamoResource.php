<?php

namespace App\Http\Resources\GuiaPrestamo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuiaPrestamoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "codigo" => $this->codigo,
            "state" => $this->state,
            "comentario" => $this->comentario,
            "encargado" => $this->user_encargado?->name,
            "created_by" => $this->creador?->name,
            "fecha_entrega" => $this->fecha_entrega?->format("Y-m-d h:i A"),
            "fecha_gestionado" => $this->fecha_gestionado?->format("Y-m-d h:i A"),
            "fecha_revisado" => $this->fecha_revisado?->format("Y-m-d h:i A"),
            "created_at" => $this->created_at->format("Y-m-d h:i A"),
            "monto_total" => round($this->detalles?->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->producto->pventa;
            }) ?? 0, 2),
            "mercaderia" => $this->detalles?->map(function ($p) {
                return [
                    "id" => $p->id,
                    "lote" => $p->lote->lote,
                    "fecha_vencimiento" => $p->lote->fecha_vencimiento,
                    "sku" => $p->producto->sku,
                    "nombre" => $p->producto->nombre,
                    "imagen" => $p->producto->imagen ?? env("IMAGE_DEFAULT"),
                    "caracteristicas" => $p->producto->caracteristicas,
                    "cantidad" => $p->cantidad,
                    "stock" => $p->stock,
                    "pventa" => $p->producto->pventa,
                    "created_at" => $p->created_at,
                    "created_by" => $p->creador->name
                ];
            }) ?? collect(),
        ];
    }
}
