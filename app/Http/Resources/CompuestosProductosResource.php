<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompuestosProductosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'compuesto' => new CompuestoResource($this->compuesto_id),
            'producto' => new ProductoResource($this->producto_id),
            'porcentaje' => $this->porcentajes,
            'creado' => $this->created_at->format('Y-m-d H:i:s'),
            'actualizado' => $this->updated_at->format('Y-m-d H:i:s'),

        ];
        return $data;
    }
}
