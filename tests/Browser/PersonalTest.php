<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PersonalTest extends DuskTestCase
{
    public function test_index_shows_h1_and_create_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal')
                ->assertSee('Lista de personal')
                ->assertSee('Crear nuevo personal');
        });
    }

    public function test_index_has_search_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal')
                ->assertSee('Buscar');
        });
    }

    public function test_index_has_table_headers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal')
                ->assertSee('Nombre')
                ->assertSee('Apellido')
                ->assertSee('DNI')
                ->assertSee('Email')
                ->assertSee('Telefono')
                ->assertSee('Centro de Costo')
                ->assertSee('Acciones');
        });
    }

    public function test_create_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/create')
                ->assertSee('Apellido:')
                ->assertSee('Nombre:')
                ->assertSee('DNI:')
                ->assertSee('Email:')
                ->assertSee('Telefono:')
                ->assertSee('Centro de Costo:');
        });
    }

    public function test_create_form_has_submit_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/create')
                ->assertSee('Crear Personal');
        });
    }

    public function test_create_form_has_centro_costo_select(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/create')
                ->assertSee('Sin centro de costo padre');
        });
    }

    public function test_edit_shows_h1(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/1/edit')
                ->assertSee('Editar Personal');
        });
    }

    public function test_edit_form_has_field_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/1/edit')
                ->assertSee('apellido:')
                ->assertSee('nombre:')
                ->assertSee('dni:')
                ->assertSee('email:')
                ->assertSee('telefono:');
        });
    }

    public function test_edit_form_has_update_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/1/edit')
                ->assertSee('Actualizar Personal');
        });
    }

    public function test_edit_form_has_nombre_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/personal/1/edit')
                ->assertInputPresent('nombre');
        });
    }
}
