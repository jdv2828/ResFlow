<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final consolidation tests for unique constraints and type_combustibles cleanup.
 *
 * Verifies:
 * - recursos.numero_factura is unique
 * - personal has centro_costo_id column
 * - user_centro_costo has composite unique (user_id, centro_costo_id)
 * - tipo_combustibles has centro_costo_id and categoria from ALTER migrations (120003, 120004)
 *
 * @group migrations
 */
class FinalConstraintsConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshTestDatabase(): void
    {
        $this->artisan('db:wipe');
        $this->artisan('migrate');
    }

    /**
     * Verify recursos.numero_factura has a unique index.
     */
    public function test_recursos_numero_factura_is_unique(): void
    {
        $indexes = Schema::getIndexes('recursos');

        $hasUniqueNumero = false;
        foreach ($indexes as $index) {
            if ($index['unique'] && in_array('numero_factura', $index['columns'])) {
                $hasUniqueNumero = true;
                break;
            }
        }

        $this->assertTrue($hasUniqueNumero, 'recursos.numero_factura should have a unique index');
    }

    /**
     * Verify personal has centro_costo_id column with FK to centro_costos.
     */
    public function test_personal_has_centro_costo_id_column(): void
    {
        $columns = Schema::getColumnListing('personal');

        $this->assertContains('centro_costo_id', $columns);

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
     * Verify user_centro_costo has composite unique on (user_id, centro_costo_id).
     */
    public function test_user_centro_costo_has_composite_unique(): void
    {
        $indexes = Schema::getIndexes('user_centro_costo');

        $hasCompositeUnique = false;
        foreach ($indexes as $index) {
            if ($index['unique']
                && count($index['columns']) === 2
                && in_array('user_id', $index['columns'])
                && in_array('centro_costo_id', $index['columns'])
            ) {
                $hasCompositeUnique = true;
                break;
            }
        }

        $this->assertTrue($hasCompositeUnique, 'user_centro_costo should have composite unique on (user_id, centro_costo_id)');
    }

    /**
     * Verify tipo_combustibles has centro_costo_id and categoria from ALTER migrations.
     */
    public function test_tipo_combustibles_has_all_columns_from_start(): void
    {
        $columns = Schema::getColumnListing('tipo_combustibles');

        $this->assertContains('id', $columns);
        $this->assertContains('nombre', $columns);
        $this->assertContains('descripcion', $columns);
        $this->assertContains('centro_costo_id', $columns);
        $this->assertContains('categoria', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
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
}
