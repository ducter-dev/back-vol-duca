<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
            'verificado' => $this->correo_verificado,
            'creado' => $this->creado->format('Y-m-d H:i:s'),
            'permissions_slugs' => $this->getAllPermissionsSlug()->toArray(),
            'actualizado' => $this->actualizado->format('Y-m-d H:i:s'),
            'deleled_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : 'N/A',
            'access_token' =>$this->access_token,
            'sign_in_at' =>$this->sign_in_at,
            'sign_in_expires_at' =>$this->sign_in_expires_at
        ];
        return $data;
    }
}
