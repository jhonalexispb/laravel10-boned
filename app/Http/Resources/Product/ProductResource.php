<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "sku" => $this->resource->sku,
            "tproducto" => $this->resource->tproducto,
            "codigobarra" => $this->resource->codigobarra,
            "unidad_id" => $this->resource->unidad_id,
            "unidad" => $this->resource->get_unidad->name,
            "laboratorio_id" => $this->resource->laboratorio_id,
            "laboratorio" => $this->resource->get_laboratorio->name,
            "nombre" => $this->resource->nombre,
            "caracteristicas" => $this->resource->caracteristicas,
            "nombre_completo" => $this->resource->nombre.' '.$this->caracteristicas,
            "categoria_id" => $this->resource->categoria_id,
            /* "categoria" => $this->resource->get_categoria ? $this->resource->get_categoria->name : null, */
            "descripcion" => $this->resource->descripcion,
            "registro_sanitario" => $this->resource->registro_sanitario,
            "codigo_digemid" => $this->resource->codigo_digemid,
            "pventa" => $this->resource->pventa ?? '0.0',
            "pcompra" => $this->resource->pcompra ?? '0.0',
            "stock" => $this->resource->stock ?? '0',
            "stock_seguridad" => $this->resource->stock_seguridad ?? '10',
            "imagen" => $this->resource->imagen ?? env("IMAGE_DEFAULT"),
            "linea_farmaceutica_id" => $this->resource->linea_farmaceutica_id,
            "linea_farmaceutica" => $this->resource->get_lineaFarmaceutica->nombre,
            "fabricante_id" => $this->resource->fabricante_id,
            "fabricante" => $this->resource->get_fabricante->nombre,
            "presentacion_id" => $this->resource->presentacion_id,
            /* "presentacion" => $this->resource->get_presentacion->name ? $this->resource->get_presentacion->name : 'Sin presentación', */
            "condicion_almacenamiento" => $this->resource->get_condicion_almacenamiento->pluck('id')->toArray(),
            "principios_activos" => $this->resource->get_principios_activos->pluck('id')->toArray(),
            "sale_boleta" => $this->resource->sale_boleta,
            /* "maneja_lotes" => $this->resource->maneja_lotes, */
            "maneja_escalas" => $this->resource->maneja_escalas,
            "promocionable" => $this->resource->promocionable,
            "state" => $this->resource->state ?? 1,
            "state_stock" => $this->resource->state_stock ?? 3,
            "created_at" => $this->resource->created_at->format("Y-m-d h:i A"),
        ];
    }
}
