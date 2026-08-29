<?php
namespace Src\Modules\Tickets\Domain\Exceptions;

use Exception;

class InsufficientLitrosException extends Exception
{
    protected $message = 'No hay suficientes litros disponibles en el ticket.';
}
