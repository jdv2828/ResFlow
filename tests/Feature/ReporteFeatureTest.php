<?php

namespace Tests\Feature;

use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TipoCombustible;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteFeatureTest extends TestCase
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

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $permisos = ['puede_ver_informes'];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }
        $adminRole->syncPermissions($permisos);
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

        $personal = Personal::firstOrCreate(
            ['dni' => '99999999'],
            ['nombre' => 'TEST', 'apellido' => 'PERSONAL', 'legajo' => 'LEG-REPORT-' . time()]
        );
        $this->personalId = $personal->id;

        TicketStatus::firstOrCreate(['id' => 1], ['nombre' => 'generado']);
        TicketStatus::firstOrCreate(['id' => 2], ['nombre' => 'finalizado']);
        TicketStatus::firstOrCreate(['id' => 3], ['nombre' => 'vencido']);
        TicketStatus::firstOrCreate(['id' => 4], ['nombre' => 'utilizado']);
        TicketStatus::firstOrCreate(['id' => 5], ['nombre' => 'eliminado']);
    }

    public function test_informe_index_muestra_formulario(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index'));

        $response->assertStatus(200);
        $response->assertSee('Vales Consumidos');
        $response->assertSee('Vales Emitidos');
    }

    public function test_informe_consumidos_sin_filtro_retorna_csv(): void
    {
        Ticket::create([
            'litros' => 10,
            'personal_id' => $this->personalId,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'finalizado_por' => $this->admin->id,
            'ticket_status_id' => 4,
            'activo' => false,
            'fecha_caducidad' => Carbon::now()->addDays(30),
            'hash' => 'test-hash-' . time(),
            'numero_automatico' => 'AUTO-' . time(),
            'litros_consumidos' => 10,
            'litros_asignados' => 10,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_consumidos_con_filtro_desde(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.download', [
            'fecha_desde' => '2024-01-01T00:00',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_consumidos_con_filtro_hasta(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.download', [
            'fecha_hasta' => '2026-12-31T23:59',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_consumidos_con_ambos_filtros(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.download', [
            'fecha_desde' => '2024-01-01T00:00',
            'fecha_hasta' => '2026-12-31T23:59',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_emitidos_sin_filtro_retorna_csv(): void
    {
        Ticket::create([
            'litros' => 10,
            'personal_id' => $this->personalId,
            'tipo_combustible_id' => $this->combustibleId,
            'centro_costo_id' => $this->centroCostoId,
            'emitido_por' => $this->admin->id,
            'finalizado_por' => null,
            'ticket_status_id' => 1,
            'activo' => true,
            'fecha_caducidad' => Carbon::now()->addDays(30),
            'hash' => 'test-hash-emitidos-' . time(),
            'numero_automatico' => 'AUTO-E-' . time(),
            'litros_consumidos' => 0,
            'litros_asignados' => 10,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.emitidos'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_emitidos_con_filtros(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.emitidos', [
            'fecha_desde' => '2024-01-01T00:00',
            'fecha_hasta' => '2026-12-31T23:59',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_informe_requiere_autenticacion(): void
    {
        $response = $this->get(route('reportes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_informe_usuario_sin_permiso_recibe_403(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('reportes.index'));
        $response->assertStatus(403);
    }
}
