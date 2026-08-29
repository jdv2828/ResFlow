<?php
namespace Src\Modules\Bolsa\Domain\Exceptions;

use Exception;

class InvalidQuantityException extends Exception
{
    protected $message = 'La cantidad de litros no puede ser cero';
}
