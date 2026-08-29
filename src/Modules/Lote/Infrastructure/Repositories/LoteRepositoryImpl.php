<?php

namespace Src\Modules\Lote\Infrastructure\Repositories;

use App\Models\Personal;
use App\Models\Lote;
use App\Models\LoteEmpleado;
use Src\Modules\Lote\Domain\Contracts\LoteRepository;
use Src\Modules\Lote\Domain\Entities\LoteEntity;

class LoteRepositoryImpl implements LoteRepository
{
    public function save(LoteEntity $lote): LoteEntity
    {
        if ($lote->id) {
            $eloquentLote = Lote::findOrFail($lote->id);
            $eloquentLote->update([
                'nombre' => $lote->nombre,
                'centro_costo_id' => $lote->centro_costo_id,
                'responsable_id' => $lote->responsable_id,
                'tipo_combustible_id' => $lote->tipo_combustible_id,
                'activo' => $lote->activo,
            ]);

            if ($lote->empleados) {
                $eloquentLote->loteEmpleados()->delete();

                foreach ($lote->empleados as $empleadoData) {
                    $empleadoId = $this->resolvePersonal($empleadoData);

                    LoteEmpleado::create([
                        'lote_id' => $eloquentLote->id,
                        'personal_id' => $empleadoId,
                        'nombre_completo' => $empleadoData['nombre_completo'] ?? null,
                        'dni' => $empleadoData['dni'] ?? null,
                        'patente' => $empleadoData['patente'] ?? null,
                        'litros' => $empleadoData['litros'] ?? 0,
                        'cantidad_vales' => $empleadoData['cantidad_vales'] ?? 1,
                        'fecha_caducidad' => $empleadoData['fecha_caducidad'] ?? null,
                    ]);
                }
            }

            return $this->toEntity($eloquentLote->fresh('loteEmpleados'));
        }

        $eloquentLote = Lote::create([
            'nombre' => $lote->nombre,
            'centro_costo_id' => $lote->centro_costo_id,
            'responsable_id' => $lote->responsable_id,
            'tipo_combustible_id' => $lote->tipo_combustible_id,
            'activo' => $lote->activo,
        ]);

        if ($lote->empleados) {
            foreach ($lote->empleados as $empleadoData) {
                $empleadoId = $this->resolvePersonal($empleadoData);

                LoteEmpleado::create([
                    'lote_id' => $eloquentLote->id,
                    'personal_id' => $empleadoId,
                    'nombre_completo' => $empleadoData['nombre_completo'] ?? null,
                    'dni' => $empleadoData['dni'] ?? null,
                    'patente' => $empleadoData['patente'] ?? null,
                    'litros' => $empleadoData['litros'] ?? 0,
                    'cantidad_vales' => $empleadoData['cantidad_vales'] ?? 1,
                    'fecha_caducidad' => $empleadoData['fecha_caducidad'] ?? null,
                ]);
            }
        }

        return $this->toEntity($eloquentLote->fresh('loteEmpleados'));
    }

    public function findById(int $id): ?LoteEntity
    {
        $lote = Lote::with('loteEmpleados')->find($id);

        if (!$lote) {
            return null;
        }

        return $this->toEntity($lote);
    }

    public function findWithLoteEmpleados(int $id): ?\App\Models\Lote
    {
        return Lote::with('loteEmpleados.personal')->find($id);
    }

    public function delete(int $id): void
    {
        $lote = Lote::findOrFail($id);
        $lote->loteEmpleados()->delete();
        $lote->delete();
    }

    private function toEntity(Lote $lote): LoteEntity
    {
        return new LoteEntity(
            id: $lote->id,
            nombre: $lote->nombre,
            responsable_id: $lote->responsable_id,
            centro_costo_id: $lote->centro_costo_id,
            tipo_combustible_id: $lote->tipo_combustible_id,
            activo: $lote->activo,
        );
    }

    private function resolvePersonal(array $data): ?int
    {
        $empleadoId = $data['personal_id'] ?? $data['id'] ?? null;

        if ($empleadoId) {
            return (int) $empleadoId;
        }

        if (empty($data['dni'])) {
            return null;
        }

        $empleado = Personal::where('dni', $data['dni'])->first();

        if ($empleado) {
            return $empleado->id;
        }

        if (empty($data['nombre_completo'])) {
            return null;
        }

        $nameParts = explode(' ', trim($data['nombre_completo']), 2);
        $nombre = $nameParts[0];
        $apellido = $nameParts[1] ?? '';

        $nuevo = Personal::create([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'dni' => $data['dni'],
            'legajo' => 'MAN-' . strtotime('now'),
        ]);

        return $nuevo->id;
    }
}
