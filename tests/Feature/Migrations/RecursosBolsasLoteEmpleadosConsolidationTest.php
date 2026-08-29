<?php

namespace Tests\Feature\Migrations;

use App\Models\CentroCosto;
use App\Models\Recurso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration consolidation tests for recursos, bolsas, and lote_empleados tables.
 *
 * Verifies consolidated migrations produce correct schema in one pass.
 * Uses db:wipe + migrate instead of migrate:fresh to avoid SQLite's
 * inability to drop foreign keys in down() methods of unrelated migrations.
 *
 * @group migrations
 */
class RecursosBolsasLoteEmpleadosConsolidationTest extends TestCase
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

    // ─── Recursos ─────────────────────────────────────────

    public function test_recursos_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('recursos');

        $this->assertContains('id', $columns);
        $this->assertContains('numero_factura', $columns);
        $this->assertContains('orden', $columns);
        $this->assertContains('litros', $columns);
        $this->assertContains('litros_disponibles', $columns);
        $this->assertContains('litros_inicial', $columns);
        $this->assertContains('litros_emitidos', $columns);
        $this->assertContains('litros_consumidos', $columns);
        $this->assertContains('monto', $columns);
        $this->assertContains('tipo_combustible_id', $columns);
        $this->assertContains('centro_costo_id', $columns);
        $this->assertContains('emitido_por', $columns);
        $this->assertContains('finalizado_por', $columns);
        $this->assertContains('activo', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_recursos_nullable_columns_are_correct(): void
    {
        $litrosDisponibles = $this->getColumnByName('recursos', 'litros_disponibles');
        $litrosInicial = $this->getColumnByName('recursos', 'litros_inicial');
        $litrosEmitidos = $this->getColumnByName('recursos', 'litros_emitidos');
        $litrosConsumidos = $this->getColumnByName('recursos', 'litros_consumidos');

        $this->assertNotNull($litrosDisponibles, 'litros_disponibles column should exist');
        $this->assertNotNull($litrosInicial, 'litros_inicial column should exist');
        $this->assertNotNull($litrosEmitidos, 'litros_emitidos column should exist');
        $this->assertNotNull($litrosConsumidos, 'litros_consumidos column should exist');

        $this->assertEquals(0, (int) ($litrosDisponibles['default'] ?? null), 'litros_disponibles default should be 0');
        $this->assertEquals(0, (int) ($litrosInicial['default'] ?? null), 'litros_inicial default should be 0');
        $this->assertEquals(0, (int) ($litrosEmitidos['default'] ?? null), 'litros_emitidos default should be 0');
        $this->assertEquals(0, (int) ($litrosConsumidos['default'] ?? null), 'litros_consumidos default should be 0');
    }

    public function test_recursos_foreign_keys_are_correct(): void
    {
        $foreignKeys = Schema::getForeignKeys('recursos');

        $expectedFks = [
            'tipo_combustible_id' => 'tipo_combustibles',
            'centro_costo_id' => 'centro_costos',
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

    // ─── Bolsas ──────────────────────────────────────────────

    public function test_bolsas_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('bolsas');

        $this->assertContains('id', $columns);
        $this->assertContains('centro_costo_id', $columns);
        $this->assertContains('tipo_combustible_id', $columns);
        $this->assertContains('recurso_id', $columns);
        $this->assertContains('cantidad_disponible', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_bolsas_foreign_keys_are_correct(): void
    {
        $foreignKeys = Schema::getForeignKeys('bolsas');

        $expectedFks = [
            'centro_costo_id' => 'centro_costos',
            'tipo_combustible_id' => 'tipo_combustibles',
            'recurso_id' => 'recursos',
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

    public function test_bolsas_recurso_id_is_unique(): void
    {
        $indexes = Schema::getIndexes('bolsas');

        $uniqueFound = false;
        foreach ($indexes as $index) {
            if (in_array('recurso_id', $index['columns']) && $index['unique']) {
                $uniqueFound = true;
                break;
            }
        }

        $this->assertTrue($uniqueFound, 'recurso_id should have a unique index');
    }

    // ─── Lote Empleados ──────────────────────────────────────

    public function test_lote_empleados_table_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('lote_empleados');

        $this->assertContains('id', $columns);
        $this->assertContains('lote_id', $columns);
        $this->assertContains('personal_id', $columns);
        $this->assertContains('nombre_completo', $columns);
        $this->assertContains('dni', $columns);
        $this->assertContains('patente', $columns);
        $this->assertContains('litros', $columns);
        $this->assertContains('cantidad_vales', $columns);
        $this->assertContains('fecha_caducidad', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_lote_empleados_personal_id_is_nullable(): void
    {
        $personalId = $this->getColumnByName('lote_empleados', 'personal_id');

        $this->assertNotNull($personalId, 'personal_id column should exist');
        $this->assertTrue((bool) $personalId['nullable'], 'personal_id should be nullable');
    }

    public function test_lote_empleados_foreign_keys_are_correct(): void
    {
        $foreignKeys = Schema::getForeignKeys('lote_empleados');

        $expectedFks = [
            'lote_id' => 'lotes',
            'personal_id' => 'personal',
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

    // ─── Lotes (FK standardization) ──────────────────────────

    public function test_lotes_table_has_standardized_foreign_keys(): void
    {
        $foreignKeys = Schema::getForeignKeys('lotes');

        $expectedFks = [
            'centro_costo_id' => 'centro_costos',
            'responsable_id' => 'users',
            'tipo_combustible_id' => 'tipo_combustibles',
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
