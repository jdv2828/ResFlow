<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReporteTest extends DuskTestCase
{
    public function test_authenticated_user_can_access_reportes_index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/reporte')
                ->assertSee('Informes')
                ->assertSee('Vales Consumidos')
                ->assertSee('Vales Emitidos');
        });
    }

    public function test_reportes_page_has_download_forms(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/reporte')
                ->assertSee('Desde')
                ->assertSee('Hasta')
                ->assertSee('Descargar');
        });
    }
}
