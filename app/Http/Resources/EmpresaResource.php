<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
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
            'descripcion' => $this->descripcion,
            'version' => $this->version,
            'rfc_contribuyente' => $this->rfc_contribuyente,
            'rfc_representante' => $this->rfc_representante,
            'proveedor' => $this->proveedor,
            'tipo_caracter' => $this->tipo_caracter,
            'num_permiso' => $this->num_permiso,
            'clave_instalacion' => $this->clave_instalacion,
            'descripcion_instalacion' => $this->descripcion_instalacion,
            'geolocalizacion_latitud' => $this->geolocalizacion_latitud,
            'geolocalizacion_longitud' => $this->geolocalizacion_longitud,
            'numero_tanques' => $this->numero_tanques,
            'numero_ductos_entradas_salidas' => $this->numero_ductos_entradas_salidas,
            'numero_ductos_distribucion' => $this->numero_ductos_distribucion,
            'fecha_hora_corte' => $this->fecha_hora_corte,
            'producto_omision' => new ProductoResource($this->productoOmision),
            'creado' => $this->creado->format('Y-m-d H:i:s'),
            'actualizado' => $this->actualizado->format('Y-m-d H:i:s'),
        ];


        $data['productos'] = ProductoResource::collection($this->productos);

        return $data;
    }
}
