<?php

namespace App\Http\Controllers;

use App\Exports\ConsumidosExport;
use App\Exports\EmitidosExport;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function downloadReport(Request $request)
    {
        $fechaDesde = $request->filled('fecha_desde') ? $request->fecha_desde : null;
        $fechaHasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : null;
        $estado = $request->filled('estado') ? (int) $request->estado : null;

        $export = new ConsumidosExport($fechaDesde, $fechaHasta, $estado);

        if ($request->input('formato') === 'xlsx') {
            return Excel::download($export, 'reporte-consumidos.xlsx');
        }

        return $this->generateCsv($export->collection(), 'reporte-consumidos.csv');
    }

    public function downloadEmitidos(Request $request)
    {
        $fechaDesde = $request->filled('fecha_desde') ? $request->fecha_desde : null;
        $fechaHasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : null;
        $estado = $request->filled('estado') ? (int) $request->estado : null;

        $export = new EmitidosExport($fechaDesde, $fechaHasta, $estado);

        if ($request->input('formato') === 'xlsx') {
            return Excel::download($export, 'reporte-emitidos.xlsx');
        }

        return $this->generateCsv($export->collection(), 'reporte-emitidos.csv');
    }

    private function generateCsv($data, string $filename)
    {
        $handle = fopen($filename, 'w+');

        fputcsv($handle, [
            "ID", "Personal", "Litros", "Litros Consumidos/Asignados",
            "Tipo Combustible", "Estación Servicio", "Estado",
            "Usuario", "Fecha", "Fecha Creación",
        ]);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row->id ?? '',
                $row->personal?->nombre_completo ?? '',
                $row->litros ?? '',
                $row->litros_consumidos ?? $row->litros_asignados ?? '',
                $row->tipoCombustible?->nombre ?? '',
                $row->centroCosto?->nombre ?? '',
                $row->ticketStatus?->nombre ?? '',
                ($row->finalizadoPor?->name ?? $row->emitidoPor?->name ?? ''),
                $row->fecha_caducidad ?? $row->created_at ?? '',
                $row->created_at ?? '',
            ]);
        }

        fclose($handle);
        return response()->download($filename, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
