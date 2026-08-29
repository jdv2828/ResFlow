<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Personal;
use App\Models\TipoCombustible;
use App\Models\Vehiculo;

class DigitalTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $ticketId)
    {
        // Obtén el ticket por su ID y solo si el campo 'activo' está en 1
        $ticket = Ticket::where('id', $ticketId)
                        ->where('activo', 1)
                        ->firstOrFail();

        // Pasa los datos necesarios a la vista
        return view('tickets.digitalTicket', [
            'ticket' => $ticket,
        ]);
    }
}
