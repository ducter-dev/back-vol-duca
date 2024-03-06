<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrestamoResource extends JsonResource
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
            'cliente_id_c' => $this->cliente_id_c,
            'cliente_id_v' => $this->cliente_id_v,
            'cliente_compra' => new ClienteResource($this->clienteCompra),
            'cliente_venta' => new ClienteResource($this->clienteVenta),
            'cantidad' => $this->cantidad,
            'fecha' => $this->created_at->format('Y-m-d'),
            'creado' => $this->created_at->format('Y-m-d H:i:s'),
            'actualizado' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
