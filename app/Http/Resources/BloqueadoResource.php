<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BloqueadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        #dd($this->usuario_id);
        $data = [
            'usuario_id' => $this->usuario_id,
            'fecha_bloqueo' => $this->fecha_bloqueo,
            'fecha_desbloqueo' => $this->fecha_desbloqueo,
            'creado' => $this->created_at,
            'actualizado' => $this->updated_at,
        ];

        return $data;
    }
}
