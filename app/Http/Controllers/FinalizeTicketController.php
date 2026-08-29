<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Event;

class FinalizeTicketController extends Controller
{
    public string $usuario_gestor;
    public string $usuario_gestor_id;
    public string $topic;
    /**
     * Handle the incoming request.
     */
    public function __invoke(Ticket $ticket)
    {

    }


}
