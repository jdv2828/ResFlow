<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration consolidation tests for users, centros de costos, and personal tables.
 * 
 * These tests verify that the consolidated migrations create the correct schema.
 * NOTE: These tests require a database connection. Run with: php artisan migrate:fresh --env=testing
 * 
 * @group migrations
 */
class UsersCentroCostosConsolidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify users table has all columns from consolidated migration.
     */
    public function test_users_table_has_two_factor_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('users');

        $this->assertContains('id', $columns);
        $this->assertContains('name', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('email_verified_at', $columns);
        $this->assertContains('password', $columns);
        $this->assertContains('two_factor_secret', $columns);
        $this->assertContains('two_factor_recovery_codes', $columns);
        $this->assertContains('two_factor_confirmed_at', $columns);
        $this->assertContains('remember_token', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    /**
     * Verify centro_costos table has all columns from consolidated migration.
     */
    public function test_centro_costos_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('centro_costos');

        $this->assertContains('id', $columns);
        $this->assertContains('nombre', $columns);
        $this->assertContains('ubicacion', $columns);
        $this->assertContains('descripcion', $columns);
        $this->assertContains('centro_padre_id', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    /**
     * Verify personal table has all columns from consolidated migration.
     */
    public function test_personal_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('personal');

        $this->assertContains('id', $columns);
        $this->assertContains('nombre', $columns);
        $this->assertContains('apellido', $columns);
        $this->assertContains('legajo', $columns);
        $this->assertContains('dni', $columns);
        $this->assertContains('foto', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('telefono', $columns);
        $this->assertContains('centro_costo_id', $columns);
        $this->assertContains('cargo_id', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    /**
     * Verify centro_costos.centro_padre_id has foreign key to centro_costos.
     */
    public function test_centro_costos_centro_padre_id_has_foreign_key(): void
    {
        $foreignKeys = Schema::getForeignKeys('centro_costos');

        $hasPadreFk = false;
        foreach ($foreignKeys as $fk) {
            if (in_array('centro_padre_id', $fk['columns']) && $fk['foreign_table'] === 'centro_costos') {
                $hasPadreFk = true;
                break;
            }
        }

        $this->assertTrue($hasPadreFk, 'centro_costos.centro_padre_id should have foreign key to centro_costos');
    }

    /**
     * Verify personal.centro_costo_id has foreign key to centro_costos.
     */
    public function test_personal_centro_costo_id_has_foreign_key(): void
    {
        $foreignKeys = Schema::getForeignKeys('personal');

        $hasCentroCostoFk = false;
        foreach ($foreignKeys as $fk) {
            if (in_array('centro_costo_id', $fk['columns']) && $fk['foreign_table'] === 'centro_costos') {
                $hasCentroCostoFk = true;
                break;
            }
        }

        $this->assertTrue($hasCentroCostoFk, 'personal.centro_costo_id should have foreign key to centro_costos');
    }

    /**
     * Verify personal.cargo_id has foreign key to cargos.
     */
    public function test_personal_cargo_id_has_foreign_key(): void
    {
        $foreignKeys = Schema::getForeignKeys('personal');

        $hasCargoFk = false;
        foreach ($foreignKeys as $fk) {
            if (in_array('cargo_id', $fk['columns']) && $fk['foreign_table'] === 'cargos') {
                $hasCargoFk = true;
                break;
            }
        }

        $this->assertTrue($hasCargoFk, 'personal.cargo_id should have foreign key to cargos');
    }

    protected function getColumnByName(string $tableName, string $columnName): ?array
    {
        $columns = Schema::getColumns($tableName);

        foreach ($columns as $column) {
            if ($column['name'] === $columnName) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Verify nullable columns are correctly set.
     */
    public function test_nullable_columns_are_correctly_set(): void
    {
        // Users nullable columns
        $this->assertTrue($this->getColumnByName('users', 'email_verified_at')['nullable']);
        $this->assertTrue($this->getColumnByName('users', 'two_factor_secret')['nullable']);
        $this->assertTrue($this->getColumnByName('users', 'two_factor_recovery_codes')['nullable']);
        $this->assertTrue($this->getColumnByName('users', 'two_factor_confirmed_at')['nullable']);

        // CentroCostos nullable columns
        $this->assertTrue($this->getColumnByName('centro_costos', 'ubicacion')['nullable']);
        $this->assertTrue($this->getColumnByName('centro_costos', 'descripcion')['nullable']);
        $this->assertTrue($this->getColumnByName('centro_costos', 'centro_padre_id')['nullable']);

        // Empleados nullable columns
        $this->assertTrue($this->getColumnByName('personal', 'dni')['nullable']);
        $this->assertTrue($this->getColumnByName('personal', 'foto')['nullable']);
        $this->assertTrue($this->getColumnByName('personal', 'email')['nullable']);
        $this->assertTrue($this->getColumnByName('personal', 'telefono')['nullable']);
        $this->assertTrue($this->getColumnByName('personal', 'centro_costo_id')['nullable']);
        $this->assertTrue($this->getColumnByName('personal', 'cargo_id')['nullable']);
    }
}
