<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_guest_sees_login_link_on_home(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Vales de Combustible')
                ->assertSee('Iniciar sesión');
        });
    }

    public function test_login_with_valid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'admin@admin.com')
                ->type('password', 'secret')
                ->press('Iniciar sesión')
                ->assertPathIs('/')
                ->assertSee('Bienvenido')
                ->assertSee('Administrador');
        });
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                ->type('email', 'wrong@example.com')
                ->type('password', 'wrongpassword')
                ->press('Iniciar sesión')
                ->pause(1000)
                ->assertPathIs('/login');
        });
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit('/')
                ->assertSee('Bienvenido')
                ->assertSee('Cerrar sesión');
        });
    }

    public function test_logout_redirects_to_home_as_guest(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->loginAs(User::find(1))
                ->visit('/')
                ->assertSee('Cerrar sesión')
                ->press('Salir')
                ->assertPathIs('/')
                ->assertSee('Iniciar sesión');
        });
    }

    public function test_accessing_protected_route_without_auth_redirects_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tickets')
                ->assertPathIs('/login');
        });
    }
}
