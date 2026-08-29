<?php

namespace Tests\Feature;

use App\Models\Bolsa;
use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Src\Modules\Tickets\Application\Dtos\ConsumeTicketDto;
use Src\Modules\Tickets\Application\Dtos\StoreTicketDto;
use Src\Modules\Tickets\Application\Dtos\UpdateTicketDto;
use Src\Modules\Tickets\Application\Services\ConsumeTicketService;
use Src\Modules\Tickets\Application\Services\DeleteTicketService;
use Src\Modules\Tickets\Application\Services\StoreTicketService;
use Src\Modules\Tickets\Application\Services\UpdateTicketService;
use Tests\TestCase;

class TicketFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private int $centroCostoId;
    private int $combustibleId;
    private int $personalId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1) ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->assertNotNull($this->admin);

        $this->actingAs($this->admin);

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

        $personal = Personal::firstOrCreate(
            ['dni' => '99999999'],
            ['nombre' => 'TEST', 'apellido' => 'PERSONAL', 'legajo' => 'LEG-TICKET-' . time()]
        );
        $this->personalId = $personal->id;

        TicketStatus::firstOrCreate(['id' => 1], ['nombre' => 'generado']);
        TicketStatus::firstOrCreate(['id' => 2], ['nombre' => 'finalizado']);
        TicketStatus::firstOrCreate(['id' => 3], ['nombre' => 'vencido']);
        TicketStatus::firstOrCreate(['id' => 4], ['nombre' => 'utilizado']);
        TicketStatus::firstOrCreate(['id' => 5], ['nombre' => 'eliminado']);
    }

    public function test_crear_ticket_aumenta_litros_emitidos(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-EMIT-' . time(),
            'orden' => 0,
            'litros' => 1000,
            'litros_disponibles' => 1000,
            'litros_inicial' => 1000,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 100000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 1000,
        ]);

        $service = app(StoreTicketService::class);
        $dto = new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        );

        $service->execute($dto);

        $recurso->refresh();
        $this->assertEquals(100.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(900.0, (float) $recurso->litros_disponibles);
    }

    public function test_consumir_ticket_aumenta_litros_consumidos(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-CONSUME-' . time(),
            'orden' => 0,
            'litros' => 500,
            'litros_disponibles' => 500,
            'litros_inicial' => 500,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $storeService = app(StoreTicketService::class);
        $storeDto = new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        );
        $ticketEntity = $storeService->execute($storeDto);

        $consumeService = app(ConsumeTicketService::class);
        $consumeDto = new ConsumeTicketDto(
            id: $ticketEntity->id,
            litros_consumidos: 60,
        );
        $consumeService->execute($consumeDto);

        $recurso->refresh();
        $this->assertGreaterThanOrEqual(60.0, (float) $recurso->litros_consumidos);
        $this->assertEquals(60.0, (float) $recurso->litros_emitidos);
    }

    public function test_eliminar_ticket_resta_litros_emitidos(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-DELETE-' . time(),
            'orden' => 0,
            'litros' => 500,
            'litros_disponibles' => 500,
            'litros_inicial' => 500,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $storeService = app(StoreTicketService::class);
        $ticketEntity = $storeService->execute(new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        ));

        $deleteService = app(DeleteTicketService::class);
        $deleteService->execute($ticketEntity->id);

        $recurso->refresh();
        $this->assertEquals(0.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(500.0, (float) $recurso->litros_disponibles);
    }

    public function test_vencer_ticket_resta_litros_emitidos(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-EXPIRE-' . time(),
            'orden' => 0,
            'litros' => 500,
            'litros_disponibles' => 500,
            'litros_inicial' => 500,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $storeService = app(StoreTicketService::class);
        $ticketEntity = $storeService->execute(new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->subDays(1)->toDateTimeString(),
        ));

        $this->artisan('tickets:check-expiration')
            ->assertSuccessful();

        $recurso->refresh();
        $this->assertEquals(0.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(500.0, (float) $recurso->litros_disponibles);
    }

    public function test_bloquea_emision_cuando_emitidos_igualan_inicial(): void
    {
        $centroCostoId = $this->centroCostoId;
        $combustibleId = $this->combustibleId;

        $recurso = Recurso::create([
            'numero_factura' => 'TEST-BLOCK-' . time(),
            'orden' => 0,
            'litros' => 100,
            'litros_disponibles' => 100,
            'litros_inicial' => 100,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 10000,
            'tipo_combustible_id' => $combustibleId,
            'centro_costo_id' => $centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $centroCostoId,
            'tipo_combustible_id' => $combustibleId,
            'cantidad_disponible' => 100,
        ]);

        $service = app(StoreTicketService::class);

        $service->execute(new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $combustibleId,
            centro_costo_id: $centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        ));

        $recurso->refresh();
        $this->assertEquals(100.0, (float) $recurso->litros_emitidos);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hay litros disponibles');

        $service->execute(new StoreTicketDto(
            litros: 10,
            personal_id: $this->personalId,
            tipo_combustible_id: $combustibleId,
            centro_costo_id: $centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        ));
    }

    public function test_editar_ticket_recalcula_contabilidad(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-UPDATE-' . time(),
            'orden' => 0,
            'litros' => 1000,
            'litros_disponibles' => 1000,
            'litros_inicial' => 1000,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 100000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 1000,
        ]);

        $storeService = app(StoreTicketService::class);
        $ticketEntity = $storeService->execute(new StoreTicketDto(
            litros: 100,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        ));

        $recurso->refresh();
        $this->assertEquals(100.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(900.0, (float) $recurso->litros_disponibles);

        $updateService = app(UpdateTicketService::class);
        $updateDto = new UpdateTicketDto(
            id: $ticketEntity->id,
            litros: 200,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            recurso_id: $recurso->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        );

        $updateService->execute($updateDto);

        $recurso->refresh();
        $this->assertEquals(200.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(800.0, (float) $recurso->litros_disponibles);
    }

    public function test_emision_multi_recurso(): void
    {
        $recurso1 = Recurso::create([
            'numero_factura' => 'TEST-MULTI-1-' . time(),
            'orden' => 0,
            'litros' => 50,
            'litros_disponibles' => 50,
            'litros_inicial' => 50,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        $recurso2 = Recurso::create([
            'numero_factura' => 'TEST-MULTI-2-' . time(),
            'orden' => 1,
            'litros' => 50,
            'litros_disponibles' => 50,
            'litros_inicial' => 50,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso1->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 50,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso2->id,
            'centro_costo_id' => $this->centroCostoId,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 50,
        ]);

        $service = app(StoreTicketService::class);
        $ticket = $service->execute(new StoreTicketDto(
            litros: 80,
            personal_id: $this->personalId,
            tipo_combustible_id: $this->combustibleId,
            centro_costo_id: $this->centroCostoId,
            emitido_por: $this->admin->id,
            fecha_caducidad: Carbon::now()->addDays(30)->toDateTimeString(),
        ));

        $recurso1->refresh();
        $recurso2->refresh();

        $this->assertEquals(0.0, (float) $recurso1->litros_disponibles, 'Primer recurso agotado');
        $this->assertEquals(50.0, (float) $recurso1->litros_emitidos);

        $this->assertEquals(20.0, (float) $recurso2->litros_disponibles, 'Segundo recurso parcial');
        $this->assertEquals(30.0, (float) $recurso2->litros_emitidos);
    }
}
