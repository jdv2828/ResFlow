<?php

namespace Tests\Feature;

use App\Models\Bolsa;
use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use App\Models\Lote;
use App\Models\LoteEmpleado;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoteFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private CentroCosto $rootCentroCosto;
    private CentroCosto $subCentroCosto;
    private int $combustibleId;
    private const CENTRO_COSTO_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1) ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->assertNotNull($this->admin);

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $lotePerms = ['puede_ver_lote', 'puede_crear_lote', 'puede_editar_lote', 'puede_borrar_lote', 'puede_generar_lote', 'puede_imprimir_lote'];
        foreach ($lotePerms as $permiso) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permiso]);
        }
        $adminRole->syncPermissions($lotePerms);
        $this->admin->syncRoles($adminRole);
        $this->actingAs($this->admin);

        TicketStatus::firstOrCreate(['id' => 1], ['nombre' => 'generado']);
        TicketStatus::firstOrCreate(['id' => 2], ['nombre' => 'finalizado']);
        TicketStatus::firstOrCreate(['id' => 3], ['nombre' => 'vencido']);
        TicketStatus::firstOrCreate(['id' => 4], ['nombre' => 'utilizado']);
        TicketStatus::firstOrCreate(['id' => 5], ['nombre' => 'eliminado']);

        $this->rootCentroCosto = CentroCosto::create([
            'nombre' => 'TEST DIRECCION',
            'descripcion' => 'Dirección de prueba',
            'centro_padre_id' => null,
        ]);

        $this->subCentroCosto = CentroCosto::create([
            'nombre' => 'TEST SECRETARIA',
            'descripcion' => 'Secretaría de prueba',
            'centro_padre_id' => $this->rootCentroCosto->id,
        ]);

        $tipoCombustible = TipoCombustible::create([
            'nombre' => 'TEST COMBUSTIBLE ' . time(),
            'centro_costo_id' => $this->rootCentroCosto->id,
            'categoria' => 'combustible',
        ]);

        $this->combustibleId = $tipoCombustible->id;
    }

    public function test_lotes_page_accesible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('lotes.index'));

        $response->assertStatus(200);
        $response->assertSee('Lotes de Combustible');
    }

    public function test_api_empleados_por_area_retorna_json(): void
    {
        $empleado = Personal::create([
            'nombre' => 'JUAN',
            'apellido' => 'PEREZ',
            'legajo' => 'LEG-' . time(),
            'email' => 'test@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/lotes/empleados-por-centro/{$this->rootCentroCosto->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $empleado->id,
            'nombre_completo' => 'JUAN PEREZ',
        ]);
    }

    public function test_crear_lote_con_empleados(): void
    {
        $empleado = Personal::create([
            'nombre' => 'PEDRO',
            'apellido' => 'GARCIA',
            'legajo' => 'LEG-LOTE-' . time(),
            'email' => 'pedro@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('lotes.store'), [
            'nombre' => 'LOTE TEST SEMANAL',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'empleados' => [
                [
                    'personal_id' => $empleado->id,
                    'litros' => 50,
                    'cantidad_vales' => 2,
                    'fecha_caducidad' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        $response->assertRedirect(route('lotes.index'));
        $response->assertSessionHas('success');

        $lote = Lote::where('nombre', 'LOTE TEST SEMANAL')->first();
        $this->assertNotNull($lote);
        $this->assertEquals(1, $lote->loteEmpleados()->count());

        $detalle = $lote->loteEmpleados()->first();
        $this->assertEquals($empleado->id, $detalle->personal_id);
        $this->assertEquals(50.0, (float) $detalle->litros);
        $this->assertEquals(2, $detalle->cantidad_vales);
    }

    public function test_editar_lote_modifica_empleados(): void
    {
        $empleado2 = Personal::create([
            'nombre' => 'LUIS',
            'apellido' => 'MARTINEZ',
            'legajo' => 'LEG-EDIT-2-' . time(),
            'email' => 'luis@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $lote = Lote::create([
            'nombre' => 'LOTE EDIT TEST',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('lotes.update', $lote->id),
            [
                'nombre' => 'LOTE EDIT TEST MODIFICADO',
                'centro_costo_id' => $this->rootCentroCosto->id,
                'responsable_id' => $this->admin->id,
                'tipo_combustible_id' => $this->combustibleId,
                'empleados' => [
                    [
                        'personal_id' => $empleado2->id,
                        'litros' => 80,
                        'cantidad_vales' => 3,
                        'fecha_caducidad' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s'),
                    ],
                ],
            ]
        );

        $response->assertRedirect(route('lotes.index'));
        $response->assertSessionHas('success');

        $lote->refresh();
        $this->assertEquals(1, $lote->loteEmpleados()->count());
        $this->assertEquals($empleado2->id, $lote->loteEmpleados()->first()->personal_id);
    }

    public function test_generar_tickets_desde_lote(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-LOTE-GEN-' . time(),
            'orden' => 0,
            'litros' => 5000,
            'litros_disponibles' => 5000,
            'litros_inicial' => 5000,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 500000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 5000,
        ]);

        $empleado = Personal::create([
            'nombre' => 'MARIA',
            'apellido' => 'GONZALEZ',
            'legajo' => 'LEG-GEN-' . time(),
            'email' => 'maria@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $lote = Lote::create([
            'nombre' => 'LOTE GENERAR TEST',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'activo' => true,
        ]);

        LoteEmpleado::create([
            'lote_id' => $lote->id,
            'personal_id' => $empleado->id,
            'litros' => 20,
            'cantidad_vales' => 3,
            'fecha_caducidad' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->post(route('lotes.generar', $lote->id));

        $response->assertRedirect(route('lotes.index'));
        $response->assertSessionHas('success');

        $ticketsCount = Ticket::where('recurso_id', $recurso->id)->count();
        $this->assertEquals(3, $ticketsCount);

        $recurso->refresh();
        $this->assertEquals(60.0, (float) $recurso->litros_emitidos);
        $this->assertEquals(4940.0, (float) $recurso->litros_disponibles);
    }

    public function test_imprimir_lote_muestra_datos(): void
    {
        $empleado = Personal::create([
            'nombre' => 'CARLOS',
            'apellido' => 'RAMIREZ',
            'legajo' => 'LEG-PRINT-' . time(),
            'email' => 'carlos@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $lote = Lote::create([
            'nombre' => 'LOTE PRINT TEST',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'activo' => true,
        ]);

        LoteEmpleado::create([
            'lote_id' => $lote->id,
            'personal_id' => $empleado->id,
            'litros' => 40,
            'cantidad_vales' => 2,
            'fecha_caducidad' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->get(route('lotes.imprimir', $lote->id));

        $response->assertStatus(200);
        $response->assertSee('LOTE PRINT TEST');
        $response->assertSee('CARLOS');
        $response->assertSee('RAMIREZ');
        $response->assertSee('80.00 L');
        $response->assertSee('2');
    }

    public function test_crear_lote_con_empleado_manual(): void
    {
        $response = $this->actingAs($this->admin)->post(route('lotes.store'), [
            'nombre' => 'LOTE MANUAL TEST',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'empleados' => [
                [
                    'personal_id' => null,
                    'nombre_completo' => 'MANUAL EMPLEADO',
                    'dni' => '99999999',
                    'patente' => 'ABC123',
                    'litros' => 30,
                    'cantidad_vales' => 1,
                    'fecha_caducidad' => null,
                ],
            ],
        ]);

        $response->assertRedirect(route('lotes.index'));
        $response->assertSessionHas('success');

        $lote = Lote::where('nombre', 'LOTE MANUAL TEST')->first();
        $this->assertNotNull($lote);
        $this->assertEquals(1, $lote->loteEmpleados()->count());

        $detalle = $lote->loteEmpleados()->first();
        $this->assertNotNull($detalle->personal_id);
        $this->assertEquals('MANUAL EMPLEADO', $detalle->nombre_completo);
        $this->assertEquals('99999999', $detalle->dni);
        $this->assertEquals('ABC123', $detalle->patente);

        $empleado = Personal::where('dni', '99999999')->first();
        $this->assertNotNull($empleado, 'resolvePersonal should auto-create Personal from DNI');
        $this->assertEquals('MANUAL', $empleado->nombre);
        $this->assertEquals('EMPLEADO', $empleado->apellido);
    }

    public function test_generar_lote_con_empleado_manual_lo_omite(): void
    {
        $recurso = Recurso::create([
            'numero_factura' => 'TEST-MANUAL-GEN-' . time(),
            'orden' => 0,
            'litros' => 500,
            'litros_disponibles' => 500,
            'litros_inicial' => 500,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $lote = Lote::create([
            'nombre' => 'LOTE MANUAL GEN',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'activo' => true,
        ]);

        LoteEmpleado::create([
            'lote_id' => $lote->id,
            'personal_id' => null,
            'nombre_completo' => 'MANUAL OMITIDO',
            'dni' => '11111111',
            'litros' => 30,
            'cantidad_vales' => 2,
            'fecha_caducidad' => Carbon::now()->addDays(7),
        ]);

        $empleado = Personal::create([
            'nombre' => 'REAL',
            'apellido' => 'EMPLEADO',
            'legajo' => 'LEG-REAL-' . time(),
            'email' => 'real@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        LoteEmpleado::create([
            'lote_id' => $lote->id,
            'personal_id' => $empleado->id,
            'litros' => 20,
            'cantidad_vales' => 3,
            'fecha_caducidad' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->post(route('lotes.generar', $lote->id));

        $response->assertRedirect(route('lotes.index'));
        $response->assertSessionHas('success', function ($value) {
            $this->assertStringContainsString('omitido', $value);
            return true;
        });

        $ticketsCount = Ticket::where('recurso_id', $recurso->id)->count();
        $this->assertEquals(3, $ticketsCount);
    }

    public function test_lote_persiste_como_plantilla(): void
    {
        $empleado = Personal::create([
            'nombre' => 'SOFIA',
            'apellido' => 'DIAZ',
            'legajo' => 'LEG-TEMP-' . time(),
            'email' => 'sofia@test.com',
            'centro_costo_id' => $this->subCentroCosto->id,
        ]);

        $lote = Lote::create([
            'nombre' => 'LOTE PLANTILLA TEST',
            'centro_costo_id' => $this->rootCentroCosto->id,
            'responsable_id' => $this->admin->id,
            'tipo_combustible_id' => $this->combustibleId,
            'activo' => true,
        ]);

        LoteEmpleado::create([
            'lote_id' => $lote->id,
            'personal_id' => $empleado->id,
            'litros' => 50,
            'cantidad_vales' => 2,
            'fecha_caducidad' => Carbon::now()->addDays(7),
        ]);

        $loteCargado = Lote::with('loteEmpleados')->find($lote->id);
        $this->assertNotNull($loteCargado);
        $this->assertEquals(1, $loteCargado->loteEmpleados->count());
        $this->assertEquals(50.0, (float) $loteCargado->loteEmpleados->first()->litros);

        $recurso = Recurso::create([
            'numero_factura' => 'TEST-TEMPLATE-GEN-' . time(),
            'orden' => 0,
            'litros' => 500,
            'litros_disponibles' => 500,
            'litros_inicial' => 500,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => 50000,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'emitido_por' => $this->admin->id,
            'activo' => true,
        ]);

        Bolsa::create([
            'recurso_id' => $recurso->id,
            'centro_costo_id' => $this->rootCentroCosto->id,
            'tipo_combustible_id' => $this->combustibleId,
            'cantidad_disponible' => 500,
        ]);

        $this->actingAs($this->admin)->post(route('lotes.generar', $lote->id));
        $ticketsCount = Ticket::where('recurso_id', $recurso->id)->count();
        $this->assertEquals(2, $ticketsCount);

        $recurso->refresh();
        $this->assertEquals(400.0, (float) $recurso->litros_disponibles);
    }
}
