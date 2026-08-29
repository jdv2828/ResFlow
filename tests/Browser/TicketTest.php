<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TicketTest extends DuskTestCase
{
    public function test_authenticated_user_can_access_tickets_index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/tickets')
                ->assertSee('Lista de vales')
                ->assertSee('Crear Vale');
        });
    }

    public function test_authenticated_user_can_access_ticket_create_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/tickets/create')
                ->assertSee('Crear ticket')
                ->assertSee('Litros');
        });
    }

    public function test_ticket_create_form_has_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/tickets/create')
                ->assertSee('Personal / Vehículo:')
                ->assertSee('Estación de Servicio')
                ->assertSee('Tipo de Combustible')
                ->assertSee('Fecha de caducidad');
        });
    }
}
