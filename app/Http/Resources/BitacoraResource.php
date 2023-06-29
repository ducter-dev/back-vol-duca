<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BitacoraResource extends JsonResource
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
            'fecha' => $this->fecha,
            'fecha_hora' => $this->fecha_hora,
            'evento_id' => $this->evento_id,
            'evento' => new EventoResource($this->evento),
            'descripcion1' => $this->descripcion1,
            'descripcion2' => $this->descripcion2,
            'descripcion3' => $this->descripcion3,
            'usuario_id' => $this->usuario_id,
            'usuario' => new UserResource($this->usuario),
            'creado' => $this->created_at->format('Y-m-d H:i:s'),
            'actualizado' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
