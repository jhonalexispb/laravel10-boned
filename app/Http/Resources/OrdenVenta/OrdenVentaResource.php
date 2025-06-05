<?php

namespace App\Http\Resources\OrdenVenta;

use Carbon\Carbon;
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
            "cliente" => $this->cliente_id ? [
                "id" => $this->cliente->id,
                "ruc" => $this->cliente->ruc->ruc ?? null,
                "razon_social" => $this->cliente->ruc->razonSocial ?? null,
                "nombre_comercial" => $this->cliente->nombre_comercial,
                /* "direccion" => $this->cliente->direccion,
                "distrito" => $this->cliente->distrito ?? (
                    optional($this->cliente->getNameDistrito->provincia->departamento)->name . '/' .
                    optional($this->cliente->getNameDistrito->provincia)->name . '/' .
                    optional($this->cliente->getNameDistrito)->name
                ),
                "deuda" => $this->cliente->deuda,
                "estado_digemid" => $this->cliente->getEstadoDigemid->nombre ?? null, */
            ] : null,
            "comprobante_id" => $this->comprobante_id ? [
                "id" => $this->comprobante->id,
                "name" => $this->comprobante->name
            ] : null,
            "total" => $this->total,
            "forma_pago" => match ($this->forma_pago) {
                1 => 'CREDITO',
                0 => 'CONTADO',
                default => null,
            },
            "comentario" => $this->comentario,
            "zona_reparto" => $this->zona_reparto,
            "transporte_id" => $this->transporte_id ? [
                "id" => $this->transporte->id,
                "name" => $this->transporte->name
            ] : null,
            "state_orden" => $this->state_orden,
            "estado_pago" => $this->estado_pago,
            "monto_pagado" => $this->monto_pagado,
            "state_fisico" => $this->state_fisico,
            "modo_entrega" => $this->modo_entrega,
            "state_seguimiento" => $this->state_seguimiento,
            /* "documento_transporte_id" => $this->documento_transporte_id, */
            "created_by" => $this->creador->name,

            "trasabilidad" => [
                'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
                'fecha_envio' => $this->fecha_envio ? Carbon::parse($this->fecha_envio)->format('d/m/Y H:i:s') : null,
                'fecha_creacion_comprobante' => $this->fecha_creacion_comprobante ? Carbon::parse($this->fecha_creacion_comprobante)->format('d/m/Y H:i:s') : null,
                'fecha_empaquetado' => $this->fecha_empaquetado ? Carbon::parse($this->fecha_empaquetado)->format('d/m/Y H:i:s') : null,
                'fecha_cargado' => $this->fecha_cargado ? Carbon::parse($this->fecha_cargado)->format('d/m/Y H:i:s') : null,
                'fecha_agencia' => $this->fecha_agencia ? Carbon::parse($this->fecha_agencia)->format('d/m/Y H:i:s') : null,
                'fecha_entregado_cliente' => $this->fecha_entregado_cliente ? Carbon::parse($this->fecha_entregado_cliente)->format('d/m/Y H:i:s') : null,
                'fecha_corroboracion' => $this->fecha_corroboracion ? Carbon::parse($this->fecha_corroboracion)->format('d/m/Y H:i:s') : null,
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
                    "total" => $p->total,
                    "created_at" => $p->created_at?->format('d/m/Y H:i:s'),
                    "created_by" => $p->creador->name ?? null,
                ];
            }) ?? collect(),

            "guia_prestamo_codigo" => $this->guia_prestamo->codigo ?? null,
        ];
    }
}
