<?php
namespace Src\Modules\Tickets\Domain\Exceptions;

use Exception;

class TicketNotFoundException extends Exception
{
    protected $message = 'El ticket no fue encontrado.';
}
