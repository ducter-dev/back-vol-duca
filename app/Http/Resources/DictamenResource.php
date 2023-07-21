<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DictamenResource extends JsonResource
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
            'rfcDictamen' => $this->rfcDictamen,
            'loteDictamen' => $this->loteDictamen,
            'folioDictamen' => $this->folioDictamen,
            'fechaInicioDictamen' => $this->fechaInicioDictamen,
            'fechaEmisionDictamen' => $this->fechaEmisionDictamen,
            'resultadoDictamen' => $this->resultadoDictamen,
            'densidad' => $this->densidad,
            'volumen' => $this->volumen,
            'cliente_id' => $this->cliente_id,
            'cliente' => new ClienteResource($this->cliente),
            'rutaDictamen' => $this->rutaDictamen,
            'balance_id' => $this->balance_id,
            'balance' => new BalanceResource($this->balance),
            'creado' => $this->created_at->format('Y-m-d H:i:s'),
            'actualizado' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
