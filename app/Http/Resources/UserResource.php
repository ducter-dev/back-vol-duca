<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'nombre' => $this->nombre,
            'usuario' => $this->usuario,
            'correo' => $this->correo,
            'roles' => $this->getRoleNames()->toArray(),
            'permissions_slugs' => $this->getAllPermissionsSlug()->toArray(),
            'creado' => $this->creado->format('Y-m-d H:i:s'),
            'actualizado' => $this->actualizado->format('Y-m-d H:i:s'),
        ];
        return $data;
    }
}
