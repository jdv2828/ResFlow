<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration consolidation tests for vehiculos and tickets tables.
 *
 * Verifies consolidated migrations produce correct schema in one pass.
 * Uses db:wipe + migrate instead of migrate:fresh to avoid SQLite's
 * inability to drop foreign keys in down() methods of unrelated migrations.
 *
 * @group migrations
 */
class VehiculosTicketsConsolidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Override to avoid migrate:fresh (SQLite can't drop FKs in down()).
     * Uses db:wipe + migrate instead.
     */
    protected function beforeRefreshingDatabase(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
    }

    protected function refreshTestDatabase(): void
    {
        $this->artisan('db:wipe');
        $this->artisan('migrate');
    }

    /**
     * Find a column definition by name from Schema::getColumns().
     * SQLite returns a numeric array, not keyed by name.
     */
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

    // ─── Vehiculos ───────────────────────────────────────────

    public function test_vehiculos_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('vehiculos');

        $this->assertContains('id', $columns);
        $this->assertContains('marca', $columns);
        $this->assertContains('modelo', $columns);
        $this->assertContains('anio', $columns);
        $this->assertContains('color', $columns);
        $this->assertContains('patente', $columns);
        $this->assertContains('foto', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_vehiculos_anio_and_color_are_nullable(): void
    {
        $anio = $this->getColumnByName('vehiculos', 'anio');
        $color = $this->getColumnByName('vehiculos', 'color');

        $this->assertNotNull($anio, 'anio column should exist');
        $this->assertNotNull($color, 'color column should exist');
        $this->assertTrue((bool) $anio['nullable'], 'anio should be nullable');
        $this->assertTrue((bool) $color['nullable'], 'color should be nullable');
    }

    // ─── Tickets ─────────────────────────────────────────────

    public function test_tickets_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('tickets');

        $this->assertContains('id', $columns);
        $this->assertContains('litros', $columns);
        $this->assertContains('personal_id', $columns);
        $this->assertContains('emitido_por', $columns);
        $this->assertContains('finalizado_por', $columns);
        $this->assertContains('tipo_combustible_id', $columns);
        $this->assertContains('centro_costo_id', $columns);
        $this->assertContains('ticket_status_id', $columns);
        $this->assertContains('activo', $columns);
        $this->assertContains('fecha_caducidad', $columns);
        $this->assertContains('hash', $columns);
        $this->assertContains('numero_automatico', $columns);
        $this->assertContains('vehiculo_id', $columns);
        $this->assertContains('litros_consumidos', $columns);
        $this->assertContains('litros_asignados', $columns);
        $this->assertContains('recurso_id', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_tickets_nullable_columns_are_correct(): void
    {
        $hash = $this->getColumnByName('tickets', 'hash');
        $numeroAutomatico = $this->getColumnByName('tickets', 'numero_automatico');
        $vehiculoId = $this->getColumnByName('tickets', 'vehiculo_id');
        $recursoId = $this->getColumnByName('tickets', 'recurso_id');

        $this->assertNotNull($hash, 'hash column should exist');
        $this->assertNotNull($numeroAutomatico, 'numero_automatico column should exist');
        $this->assertNotNull($vehiculoId, 'vehiculo_id column should exist');
        $this->assertNotNull($recursoId, 'recurso_id column should exist');
        $this->assertTrue((bool) $hash['nullable'], 'hash should be nullable');
        $this->assertTrue((bool) $numeroAutomatico['nullable'], 'numero_automatico should be nullable');
        $this->assertTrue((bool) $vehiculoId['nullable'], 'vehiculo_id should be nullable');
        $this->assertTrue((bool) $recursoId['nullable'], 'recurso_id should be nullable');
    }

    public function test_tickets_default_values_are_correct(): void
    {
        $litrosConsumidos = $this->getColumnByName('tickets', 'litros_consumidos');
        $litrosAsignados = $this->getColumnByName('tickets', 'litros_asignados');

        $this->assertNotNull($litrosConsumidos, 'litros_consumidos column should exist');
        $this->assertNotNull($litrosAsignados, 'litros_asignados column should exist');
        $this->assertEquals(0, (int) ($litrosConsumidos['default'] ?? null), 'litros_consumidos default should be 0');
        $this->assertEquals(0, (int) ($litrosAsignados['default'] ?? null), 'litros_asignados default should be 0');
    }

    public function test_tickets_foreign_keys_are_correct(): void
    {
        $foreignKeys = Schema::getForeignKeys('tickets');

        $expectedFks = [
            'personal_id' => 'personal',
            'vehiculo_id' => 'vehiculos',
            'recurso_id' => 'recursos',
            'tipo_combustible_id' => 'tipo_combustibles',
            'centro_costo_id' => 'centro_costos',
            'ticket_status_id' => 'ticket_statuses',
            'emitido_por' => 'users',
            'finalizado_por' => 'users',
        ];

        foreach ($expectedFks as $column => $table) {
            $found = false;
            foreach ($foreignKeys as $fk) {
                if (in_array($column, $fk['columns']) && $fk['foreign_table'] === $table) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "{$column} should have foreign key to {$table}");
        }
    }
}
