<?php

namespace Tests\Feature;

use App\Models\CentroCosto;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Src\Modules\Recurso\Application\Dtos\StoreRecursoDto;
use Src\Modules\Recurso\Application\Services\StoreRecursoService;
use Tests\TestCase;

class RecursoFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private int $centroCostoId;
    private int $combustibleId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1) ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->assertNotNull($this->admin, 'Admin user must exist in DB');

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $recursoPerms = ['puede_ver_recurso', 'puede_crear_recurso', 'puede_editar_recurso', 'puede_borrar_recurso'];
        foreach ($recursoPerms as $permiso) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permiso]);
        }
        $adminRole->syncPermissions($recursoPerms);
        $this->admin->syncRoles($adminRole);

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

    public function test_recursos_page_muestra_acumuladores(): void
    {
        $response = $this->actingAs($this->admin)->get(route('recursos.index'));

        $response->assertStatus(200);
        $response->assertSee('Recursos');
    }

    public function test_crear_recurso_inicializa_contadores(): void
    {
        $service = app(StoreRecursoService::class);

        $dto = new StoreRecursoDto(
            litros: 5000,
            litros_disponibles: 5000,
            monto: 1000000,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            numero_factura: 'TEST-' . time(),
            orden: 1,
            emitido_por: $this->admin->id,
        );

        $entity = $service->execute($dto);

        $this->assertNotNull($entity->id);

        $recurso = Recurso::find($entity->id);

        $this->assertEquals(5000.0, (float) $recurso->litros_inicial);
        $this->assertEquals(5000.0, (float) $recurso->litros);
        $this->assertEquals(5000.0, (float) $recurso->litros_disponibles);
        $this->assertEquals(0.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(0.0, (float) $recurso->litros_consumidos);
    }

    public function test_bolsa_puede_recibir_devolucion_de_tickets(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-RETURN-' . time(),
            'orden' => 0,
            'litros' => 1000,
            'litros_disponibles' => 500,
            'litros_inicial' => 1000,
            'litros_emitidos' => 500,
            'litros_consumidos' => 0,
            'monto' => 100000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        $bolsa = \App\Models\Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $service = app(\Src\Modules\Bolsa\Application\Services\IncreaseLitersService::class);
        $dto = new \Src\Modules\Bolsa\Application\Dtos\FuelLitersDto(
            litros: 100,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            recurso_id: $recurso->id
        );

        $service->execute($dto);

        $bolsa->refresh();
        $this->assertEquals(600.0, (float) $bolsa->cantidad_disponible);
    }
}
