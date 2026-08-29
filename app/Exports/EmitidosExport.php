<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmitidosExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $fechaDesde = null,
        private ?string $fechaHasta = null,
        private ?int $ticketStatusId = null,
    ) {}

    public function collection()
    {
        $query = Ticket::with(['personal', 'tipoCombustible', 'centroCosto', 'emitidoPor'])
            ->where('activo', 1);

        if (!auth()->user()->hasRole('admin')) {
            $query->where('emitido_por', auth()->id());
        }

        if ($this->fechaDesde) {
            $query->where('created_at', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->where('created_at', '<=', $this->fechaHasta);
        }

        if ($this->ticketStatusId) {
            $query->where('ticket_status_id', $this->ticketStatusId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Personal',
            'Litros',
            'Litros Asignados',
            'Tipo Combustible',
            'Estación Servicio',
            'Estado',
            'Emitido Por',
            'Fecha Vencimiento',
            'Fecha Creación',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->personal?->nombre_completo ?? 'Sin personal',
            $ticket->litros,
            $ticket->litros_asignados,
            $ticket->tipoCombustible?->nombre ?? '',
            $ticket->centroCosto?->nombre ?? '',
            $ticket->ticketStatus?->nombre ?? '',
            $ticket->emitidoPor?->name ?? '',
            $ticket->fecha_caducidad ?? '',
            $ticket->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
