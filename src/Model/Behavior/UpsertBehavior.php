<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Model\Behavior;

use Cake\Database\Expression\FunctionExpression;
use Cake\ORM\Behavior;

/**
 * Upsert Behavior
 */
class UpsertBehavior extends Behavior
{
    /**
     * @var array
     */
    protected $_defaultConfig = [
        'updateColumns' => null,
        'uniqueKey' => null,
    ];

    /**
     * Execute bulk upsert query.
     *
     * The options array accept the following keys:
     *  - uniqueKey: List of fields which defines unique key. If not in $options or behavior config, primary key is used.
     *  - updateColumns: List of fields that will be updated on conflict. Pass `*` to set all table columns.
     *  - extra: Extra fields which will be appended to data.
     *  - returning: List of fields that will be returned in statement. If empty, method returns the number of rows changed.
     *
     * @param array $data Upsert data
     * @param array $options Options
     * @return int|\Cake\Database\StatementInterface Returns the number of rows changed or database statement.
     */
    public function bulkUpsert(array $data, array $options = [])
    {
        if (empty($data)) {
            return 0;
        }
        $primaryKey = $this->_table->getPrimaryKey();
        $options += [
            'uniqueKey' => $this->getConfig('uniqueKey', $primaryKey),
            'updateColumns' => $this->getConfig('updateColumns', []),
            'extra' => [],
            'returning' => [],
        ];

        $tableColumns = $this->_table->getSchema()->columns();
        $uniqueKey = (array)$options['uniqueKey'];
        $updateColumns = $options['updateColumns'];
        $extra = $options['extra'];
        $returning = $options['returning'];
        if ($updateColumns === '*') {
            $updateColumns = array_diff($tableColumns, $uniqueKey);
        }
        $sequenceName = null;
        if (count($uniqueKey) === 1 && reset($uniqueKey) === $primaryKey) {
            $sequenceName = $this->_table->find()
                                         ->select(['sequence' => 'pg_get_serial_sequence(:table, :id)'])
                                         ->bind(':table', $this->_table->getTable())
                                         ->bind(':id', $primaryKey)
                                         ->first()->sequence ?? null;
        }
        $updateColumns = array_filter((array)$updateColumns, function ($column) use ($tableColumns) {
            return in_array($column, $tableColumns);
        });
        $extra = array_filter($extra, function ($column) use ($tableColumns) {
            return in_array($column, $tableColumns);
        }, ARRAY_FILTER_USE_KEY);
        $fields = [];
        foreach ($data as $row) {
            $fields = array_flip(array_flip(array_merge($fields, array_keys($row))));
        }
        $fields = array_filter(
            $fields,
            function ($key) use ($updateColumns) {
                return in_array($key, $updateColumns);
            }
        );
        $fields = array_unique(array_merge($uniqueKey, $fields, array_keys($extra)));
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "$column = EXCLUDED.$column";
        }
        $conflictKey = implode(',', $uniqueKey);
        $epilog = "ON CONFLICT ($conflictKey)";
        if (empty($updateColumns)) {
            $epilog .= ' DO NOTHING';
        } else {
            $epilog .= ' DO UPDATE SET ' . implode(',', $updateValues);
        }
        if (!empty($returning)) {
            $epilog .= ' RETURNING ' . implode(',', $returning);
        }

        $query = $this->_table
            ->query()
            ->insert(array_values($fields))
            ->epilog($epilog);

        foreach ($data as $row) {
            $filtered = array_filter(
                $row,
                function ($key) use ($updateColumns, $uniqueKey) {
                    return in_array($key, $updateColumns) || in_array($key, $uniqueKey);
                },
                ARRAY_FILTER_USE_KEY
            );
            $values = array_merge($extra, $filtered);
            if ($sequenceName && in_array($primaryKey, $fields) && empty($values[$primaryKey])) {
                $values[$primaryKey] = new FunctionExpression('nextval', [$sequenceName]);
            }
            $query->values($values);
        }

        if (empty($returning)) {
            return $query->rowCountAndClose();
        } else {
            return $query->execute();
        }
    }
}
