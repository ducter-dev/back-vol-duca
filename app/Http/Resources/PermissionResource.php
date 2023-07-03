<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'name' => $this->name,
            'created_at' => $this->created_at->format('d-m-Y h:i:s'),
            'created_format' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('d-m-Y h:i:s'),
            'updated_format' => $this->updated_at->diffForHumans(),
        ];


        return $data;
    }
}
