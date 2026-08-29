<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoteTest extends DuskTestCase
{
    public function test_authenticated_user_can_access_lotes_index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/lotes')
                ->assertSee('Lotes de Combustible')
                ->assertSee('Crear Lote');
        });
    }

    public function test_authenticated_user_can_access_lote_create_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/lotes/create')
                ->assertSee('Crear Lote de Combustible')
                ->assertSee('Dirección')
                ->assertSee('Estación de Servicio')
                ->assertSee('Tipo de Combustible');
        });
    }

    public function test_lote_create_form_has_employee_table_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/lotes/create')
                ->assertSee('Personal del Lote')
                ->assertSee('Agregar Personal Manual')
                ->assertSee('Guardar Lote');
        });
    }
}
