<?php
namespace Src\Modules\Recurso\Domain\Exceptions;

use Exception;

class RecursoNotFoundException extends Exception
{
    protected $message = 'El recurso no fue encontrado.';
}
