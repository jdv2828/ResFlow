<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RecursoTest extends DuskTestCase
{
    public function test_index_shows_h1_and_create_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos')
                ->assertSee('Recursos')
                ->assertSee('Crear Recurso');
        });
    }

    public function test_index_has_search_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos')
                ->assertSee('Buscar');
        });
    }

    public function test_index_has_table_headers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos')
                ->assertSee('Nro de Factura')
                ->assertSee('Estado')
                ->assertSee('Litros')
                ->assertSee('Monto')
                ->assertSee('Estación')
                ->assertSee('Tipo de combustible')
                ->assertSee('Acciones');
        });
    }

    public function test_create_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/create')
                ->assertSee('Número de Factura')
                ->assertSee('Litros')
                ->assertSee('monto')
                ->assertSee('Estación de Servicio')
                ->assertSee('Tipo de Combustible');
        });
    }

    public function test_create_form_has_submit_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/create')
                ->assertSee('Crear Recurso');
        });
    }

    public function test_create_form_has_centro_costo_select(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/create')
                ->assertSee('Seleccione un centro de costo');
        });
    }

    public function test_create_form_has_tipo_combustible_select(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/create')
                ->assertSee('Seleccione un tipo de combustible');
        });
    }

    public function test_edit_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/3/edit')
                ->assertSee('Número de Factura')
                ->assertSee('Litros')
                ->assertSee('monto')
                ->assertSee('Estación de Servicio')
                ->assertSee('Tipo de Combustible');
        });
    }

    public function test_edit_form_has_update_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/3/edit')
                ->assertSee('Actualizar Recurso');
        });
    }

    public function test_edit_form_has_numero_expediente_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/recursos/3/edit')
                ->assertInputPresent('numero_factura');
        });
    }
}
