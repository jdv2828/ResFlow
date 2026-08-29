<?php
namespace Src\Modules\Recurso\Domain\Exceptions;

use Exception;

class CombustibleNotFoundInEstacion extends Exception
{
    protected $message = 'El combustible no fue encontrado. Verifique que el combustible exista en la estacion';
    protected $code = 404;
}
