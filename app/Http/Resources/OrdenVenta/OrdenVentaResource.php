<?php

namespace App\Http\Resources\OrdenVenta;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenVentaResource extends JsonResource
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
            "cliente_id" => $this->cliente_id,
            "comprobante_id" => $this->comprobante_id,
            "total" => $this->total,
            "formaPago" => $this->formaPago,
            "forma_facturacion_id" => $this->forma_facturacion_id,
            "comentario" => $this->comentario,
            "zona_reparto" => $this->zona_reparto,
            "transporte_id" => $this->transporte_id,
            "state_orden" => $this->state_orden,
            "estado_pago" => $this->estado_pago,
            "monto_pagado" => $this->monto_pagado,
            "state_fisico" => $this->state_fisico,
            "state_seguimiento" => $this->state_seguimiento,
            "documento_transporte_id" => $this->documento_transporte_id,
            "created_by" => $this->created_by,
            "created_at" => $this->created_at,

            "trasabilidad" => [
                'created_at' => $this->created_at,
                'fecha_envio' => $this->fecha_envio,
                'fecha_creacion_comprobante' => $this->fecha_creacion_comprobante,
                'fecha_empaquetado' => $this->fecha_empaquetado,
                'fecha_cargado' => $this->fecha_cargado,
                'fecha_agencia' => $this->fecha_agencia,
                'fecha_entregado_cliente' => $this->fecha_entregado_cliente,
                'fecha_corroboracion' => $this->fecha_corroboracion,
            ],

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
                    "pventa" => $p->pventa,
                    "total" => $p->total, // este reemplaza el pventa duplicado
                    "created_at" => $p->created_at,
                    "created_by" => $p->creador->name ?? null,
                ];
            }) ?? collect(),
        ];
    }
}
