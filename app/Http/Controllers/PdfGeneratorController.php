<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use PDF;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class PdfGeneratorController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        // Encuentra el ticket por su ID o lanza un error 404 si no se encuentra
        $ticket = Ticket::with(['personal.centroCosto', 'tipoCombustible', 'centroCosto', 'emitidoPor', 'recurso'])
        ->where('id', $id)
        ->where('activo', 1)
        ->firstOrFail();

        $biblioteca = new DNS1D();
        $biblioteca->setStorPath(__DIR__ . '/cache/');

        $biblioteca2 = new DNS2D();
        $biblioteca2->setStorPath(__DIR__ . '/cache/');

        $baseUrl = config('app.digital_ticket_url');//buscar en app.php

        $qr = $biblioteca2->getBarcodeHTML("$baseUrl/$id", 'PDF417', 2, 2);

        $barcode = $biblioteca->getBarcodeHTML("$id", 'C128B');

        // Genera el PDF con la vista 'tickets.pdfTicket' y pasa el ticket y el código de barras
        $pdf = PDF::loadView('tickets.pdfTicket', compact('ticket', 'barcode','qr'));

        // Descarga el PDF con el nombre 'ticket.pdf'
        return $pdf->stream('ticket.pdf');
    }
}
