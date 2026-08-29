<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class VehiculoTest extends DuskTestCase
{
    public function test_index_shows_h1_and_create_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos')
                ->assertSee('Lista de Vehículos')
                ->assertSee('Crear Nuevo Vehículo');
        });
    }

    public function test_index_has_search_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos')
                ->assertSee('Buscar');
        });
    }

    public function test_index_has_table_headers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos')
                ->assertSee('Marca')
                ->assertSee('Modelo')
                ->assertSee('Patente')
                ->assertSee('Acciones');
        });
    }

    public function test_create_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/create')
                ->assertSee('Marca:')
                ->assertSee('Modelo:')
                ->assertSee('Patente:')
                ->assertSee('Chofer:');
        });
    }

    public function test_create_form_has_submit_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/create')
                ->assertSee('Crear Vehículo');
        });
    }

    public function test_create_form_has_foto_field(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/create')
                ->assertSee('Foto:');
        });
    }

    public function test_edit_shows_h1(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/1/edit')
                ->assertSee('Editar Vehículo');
        });
    }

    public function test_edit_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/1/edit')
                ->assertSee('Marca:')
                ->assertSee('Modelo:')
                ->assertSee('Patente:')
                ->assertSee('Chofer:');
        });
    }

    public function test_edit_form_has_update_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/1/edit')
                ->assertSee('Actualizar Vehículo');
        });
    }

    public function test_edit_form_has_marca_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/vehiculos/1/edit')
                ->assertInputPresent('marca');
        });
    }
}
