<?php

namespace App\Http\Resources\OrdenVenta;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrdenVentaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return OrdenVentaResource::collection($this->collection)->resolve();
    }
}
