<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuditoriaTest extends DuskTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/auditoria')
                ->assertPathIs('/login');
        });
    }

    public function test_non_admin_user_gets_403(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $user = User::factory()->create();
            $browser->loginAs($user)
                ->visit('/auditoria')
                ->assertSee('403');
        });
    }

    public function test_admin_sees_page_title(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria')
                ->assertSee('Auditoría de actividad');
        });
    }

    public function test_admin_sees_metric_cards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria')
                ->assertSee('Hoy')
                ->assertSee('Últimos 7 días')
                ->assertSee('Últimos 30 días')
                ->assertSee('Usuarios únicos');
        });
    }

    public function test_admin_sees_filter_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria')
                ->assertSee('Buscar')
                ->assertSee('Acción')
                ->assertSee('Usuario')
                ->assertSee('Tipo')
                ->assertSee('Desde')
                ->assertSee('Hasta');
        });
    }

    public function test_admin_sees_action_buttons(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria')
                ->assertSee('Filtrar')
                ->assertSee('Exportar CSV')
                ->assertSee('Limpiar');
        });
    }

    public function test_admin_sees_table_headers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria')
                ->assertSee('Fecha')
                ->assertSee('Acción')
                ->assertSee('Sujeto')
                ->assertSee('Datos');
        });
    }

    public function test_recurso_timeline_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria/recursos/1/timeline')
                ->assertSee('Timeline de recurso')
                ->assertSee('Volver a auditoría');
        });
    }

    public function test_recurso_timeline_has_export_pdf(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria/recursos/1/timeline')
                ->assertSee('Exportar PDF');
        });
    }

    public function test_ticket_timeline_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria/tickets/1/timeline')
                ->assertSee('Timeline de ticket')
                ->assertSee('Volver a auditoría');
        });
    }

    public function test_ticket_timeline_has_back_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/auditoria/tickets/1/timeline')
                ->assertSee('Volver a auditoría');
        });
    }

    public function test_guest_timeline_redirects_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/auditoria/recursos/1/timeline')
                ->assertPathIs('/login');
        });
    }
}
