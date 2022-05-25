<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Schema;

use Cake\Database\Exception;
use Cake\Database\Schema\Collection;
use Cake\Database\Schema\TableSchema;

class MaterializedViewCollection extends Collection
{
    /**
     * Get the list of materialized views available in the current connection.
     *
     * @return string[] The list of materialized views in the connected database/schema.
     */
    public function listTables(): array
    {
        $config = $this->_connection->config();
        $sql = 'SELECT matviewname as name FROM pg_catalog.pg_matviews WHERE schemaname = ? ORDER BY name';
        $schema = empty($config['schema']) ? 'public' : $config['schema'];
        $result = [];
        $statement = $this->_connection->execute($sql, [$schema]);
        while ($row = $statement->fetch()) {
            $result[] = $row[0];
        }
        $statement->closeCursor();

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function _reflect(string $stage, string $name, array $config, TableSchema $schema): void
    {
        if ($stage === 'Column') {
            $this->_reflectColumn($name, $config, $schema);
        } elseif ($stage === 'ForeignKey') {
            return;
        }
        parent::_reflect($stage, $name, $config, $schema);
    }

    /**
     * Helper method for "Column" step of the reflection process.
     *
     * @param string $name The table name.
     * @param array $config The config data.
     * @param \Cake\Database\Schema\TableSchema $schema The table schema instance.
     * @return void
     * @throws \Cake\Database\Exception on query failure.
     * @uses \Cake\Database\Schema\SchemaDialect::convertColumnDescription
     */
    protected function _reflectColumn(string $name, array $config, TableSchema $schema): void
    {
        [$sql, $params] = $this->_describeColumnSql($name, $config);

        try {
            $statement = $this->_connection->execute($sql, $params);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
        /** @psalm-suppress PossiblyFalseIterator */
        foreach ($statement->fetchAll('assoc') as $row) {
            $this->_dialect->convertColumnDescription($schema, $row);
        }
        $statement->closeCursor();
    }

    /**
     * Generate the SQL to describe a materialized view.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config The connection configuration.
     * @return array An array of (sql, params) to execute.
     */
    protected function _describeColumnSql(string $tableName, array $config): array
    {
        $sql = 'SELECT
            s.nspname AS schema,
            a.attname AS name,
            pg_catalog.format_type(a.atttypid, a.atttypmod) AS type,
            case when a.attnotnull then \'YES\' else \'NO\' end AS "null",
            null AS "default",
            null AS collation_name,
            null AS comment,
            null AS char_length,
            null AS column_precision,
            null AS column_scale
        FROM pg_attribute a
        JOIN pg_class t ON a.attrelid = t.oid
        JOIN pg_namespace s ON t.relnamespace = s.oid
        WHERE a.attnum > 0
        AND NOT a.attisdropped
        AND t.relname = ?
        AND s.nspname = ?
        ORDER BY a.attnum';

        $schema = empty($config['schema']) ? 'public' : $config['schema'];

        return [$sql, [$tableName, $schema]];
    }
}
