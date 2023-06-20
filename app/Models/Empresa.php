<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'descripcion',
        'rfc_contribuyente',
        'rfc_representante',
        'proveedor',
        'tipo_caracter',
        'modalidad_permiso',
        'num_permiso',
        'clave_instalacion',
        'descripcion_instalacion',
        'geolocalizacion_latitud',
        'geolocalizacion_longitud',
        'numero_tanques',
        'numero_ductos_entradas_salidas',
        'numero_ductos_distribucion',
        'fecha_hora_corte',
        'producto_omision'
    ];

    const CREATED_AT = 'creado';
    const UPDATED_AT = 'actualizado';

    public function usuarios()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'productos_empresas', 'producto_id', '');
    }

    public function productoOmision()
    {
        return $this->belongsTo(Producto::class, 'producto_omision');
    }

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }
}
