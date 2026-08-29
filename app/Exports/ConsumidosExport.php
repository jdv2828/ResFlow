<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConsumidosExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $fechaDesde = null,
        private ?string $fechaHasta = null,
        private ?int $ticketStatusId = null,
    ) {}

    public function collection()
    {
        $query = Ticket::with(['personal', 'tipoCombustible', 'centroCosto', 'finalizadoPor'])
            ->where('activo', 0);

        if (!auth()->user()->hasRole('admin')) {
            $query->where('finalizado_por', auth()->id());
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
            'Litros Consumidos',
            'Tipo Combustible',
            'Estación Servicio',
            'Estado',
            'Finalizado Por',
            'Fecha Creación',
            'Fecha Actualización',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->personal?->nombre_completo ?? 'Sin personal',
            $ticket->litros,
            $ticket->litros_consumidos,
            $ticket->tipoCombustible?->nombre ?? '',
            $ticket->centroCosto?->nombre ?? '',
            $ticket->ticketStatus?->nombre ?? '',
            $ticket->finalizadoPor?->name ?? '',
            $ticket->created_at?->format('Y-m-d H:i:s') ?? '',
            $ticket->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
