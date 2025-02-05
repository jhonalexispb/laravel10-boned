<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
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
            "sku" => $this->sku,
            "tproducto" => $this->tproducto,
            "codigobarra" => $this->codigobarra,
            "unidad_id" => $this->unidad_id,
            "unidad" => $this->get_unidad->name,
            "laboratorio_id" => $this->laboratorio_id,
            "laboratorio" => $this->get_laboratorio->name,
            "nombre" => $this->nombre,
            "caracteristicas" => $this->caracteristicas,
            "categoria_id" => $this->categoria_id,
            "categoria" => $this->get_categoria->name,
            "descripcion" => $this->descripcion,
            "registro_sanitario" => $this->registro_sanitario,
            "pventa" => $this->pventa,
            "pcompra" => $this->pcompra,
            "stock" => $this->stock,
            "stock_seguridad" => $this->stock_seguridad,
            "imagen" => $this->imagen ?? env("IMAGE_DEFAULT"),
            "linea_farmaceutica_id" => $this->linea_farmaceutica_id,
            "linea_farmaceutica" => $this->get_lineaFarmaceutica->nombre,
            "fabricante_id" => $this->fabricante_id,
            "fabricante" => $this->get_fabricante->nombre,
            "condicion_almacenamiento" => $this->get_condicion_almacenamiento->pluck('id')->toArray(),
            "principios_activos" => $this->get_principios_activos->pluck('id')->toArray(),
            "sale_boleta" => $this->sale_boleta,
            "state" => $this->state,
            "created_at" => $this->created_at->format("Y-m-d h:i A"),
        ];
    }
}
