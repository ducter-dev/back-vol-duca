<?php

namespace App\Enums;

enum PermisosGroupEnum: string
{

    case SISTEMA = 'sistema';
    case EMPRESAS = 'empresas';
    case ROLES = 'roles';
    case PERMISOS = 'permisos';


    /**
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            PermisosGroupEnum::SISTEMA => 'Sistema',
            PermisosGroupEnum::EMPRESAS => 'Empresas',
            PermisosGroupEnum::ROLES => 'Roles',
            PermisosGroupEnum::PERMISOS => 'Permisos'
        };
    }

    /**
     * @return string
     */

    public function label(): string
    {
        return static::getLabel($this);
    }

    /**
     * @param PermisosGroupEnum $value
     * @return string
     */
    public static function getLabel(self $value): string
    {
        return match ($value) {
            PermisosGroupEnum::SISTEMA => 'sistema',
            PermisosGroupEnum::EMPRESAS => 'empresa',
            PermisosGroupEnum::ROLES => 'roles',
            PermisosGroupEnum::PERMISOS => 'permisos',
        };
    }

    /**
     * @return array
     */
    public static function getValuesFromKey($key)
    {
        $res = null;

        switch ($key) {
            case PermisosGroupEnum::SISTEMA->value:
                $res = [
                    'id' => PermisosGroupEnum::SISTEMA->value,
                    'text' => 'Sistema',
                    'name' => 'sistema',
                    'description' => 'Gestión y control de funciones del sistema.'
                ];
                break;
            case PermisosGroupEnum::EMPRESAS->value:
                $res = [
                    'id' => PermisosGroupEnum::EMPRESAS->value,
                    'text' => 'Empresa',
                    'name' => 'empresa',
                    'description' => 'Gestión y control de Configuración.'
                ];
                break;
            case PermisosGroupEnum::ROLES->value:
                $res = [
                    'id' => PermisosGroupEnum::ROLES->value,
                    'text' => 'Roles',
                    'name' => 'roles',
                    'description' => 'Gestión y control de módulo de Roles.'
                ];
                break;
            case PermisosGroupEnum::PERMISOS->value:
                $res = [
                    'id' => PermisosGroupEnum::PERMISOS->value,
                    'text' => 'Permisos',
                    'name' => 'permisos',
                    'description' => 'Gestión y control de Módulo de Permisos.'
                ];
                break;
        }
        return $res;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getValues()
    {
        $res = collect();

        $values = self::cases();

        foreach ($values as $value) {
            switch ($value) {
                case self::SISTEMA:
                    $res->push([
                        'id' => PermisosGroupEnum::SISTEMA->value,
                        'text' => 'Sistema',
                        'name' => 'sistema',
                        'description' => 'Gestión y control de funciones del sistema.'
                    ]);
                    break;
                case self::EMPRESAS:
                    $res->push([
                        'id' => PermisosGroupEnum::EMPRESAS->value,
                        'text' => 'Empresas',
                        'name' => 'empresas',
                        'description' => 'Gestión y control de Configuración.'
                    ]);
                    break;
                case self::ROLES:
                    $res->push([
                        'id' => PermisosGroupEnum::ROLES->value,
                        'text' => 'Roles',
                        'name' => 'roles',
                        'description' => 'Gestión y control de funciones de los roles.'
                    ]);
                    break;
                case self::PERMISOS:
                    $res->push([
                        'id' => PermisosGroupEnum::PERMISOS->value,
                        'text' => 'Permisos',
                        'name' => 'permisos',
                        'description' => 'Gestión y control de Permisos.'
                    ]);
                    break;
            }
        }
        return $res;
    }
}
