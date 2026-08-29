<?php

namespace Tests\Feature;

use App\Models\Bolsa;
use App\Models\CentroCosto;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Src\Modules\Bolsa\Domain\Entities\BolsaEntity;
use Src\Modules\Bolsa\Domain\ValueObjects\CantidadDisponibleValue;
use Src\Modules\Bolsa\Infrastructure\Repositories\BolsaRepositoryImpl;
use SebastianBergmann\LinesOfCode\NegativeValueException;
use Tests\TestCase;

class BolsaDecreaseNegativeTest extends TestCase
{
    use DatabaseTransactions;

    private BolsaRepositoryImpl $repository;
    private int $centroCostoId;
    private int $combustibleId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(BolsaRepositoryImpl::class);

        $centroCosto = CentroCosto::firstOrCreate(
            ['nombre' => 'TEST CENTRO COSTO'],
            ['descripcion' => 'Centro de costo para tests']
        );
        $this->centroCostoId = $centroCosto->id;

        $tipoCombustible = TipoCombustible::firstOrCreate(
            ['nombre' => 'TEST COMBUSTIBLE'],
            ['centro_costo_id' => $this->centroCostoId, 'categoria' => 'combustible']
        );
        $this->combustibleId = $tipoCombustible->id;
    }

    public function test_bolsa_no_puede_ser_negativa(): void
    {
        $user = User::factory()->create();

        $recurso = Recurso::create([
            'numero_factura' => 'TEST-NEG-' . time(),
            'orden' => 0,
            'litros' => 100,
            'litros_disponibles' => 100,
            'litros_inicial' => 100,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 10000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $user->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 100,
        ]);

        // Increase first (devuelta de ticket) - should work because total is still <= initial
        $this->repository->increaseLiters(new BolsaEntity(
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            cantidad_disponible: new CantidadDisponibleValue(50),
            recurso_id: $recurso->id,
        ));

        $bolsa = Bolsa::where('recurso_id', $recurso->id)->first();
        $this->assertEquals(150.0, (float) $bolsa->cantidad_disponible);

        // Decrease by more than available
        $this->expectException(NegativeValueException::class);
        $this->repository->decreaseLiters(new BolsaEntity(
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            cantidad_disponible: new CantidadDisponibleValue(200),
            recurso_id: $recurso->id,
        ));
    }
}
