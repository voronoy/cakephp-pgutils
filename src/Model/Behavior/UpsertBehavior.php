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
     *
     * - uniqueKey: List of fields which defines unique key.
     * - updateColumns: List of fields that will be updated on conflict.
     * - extra: Extra fields which will be appended to data.
     * - returning: List of fields that will be returned in statement. If empty, method returns the number of rows changed.
     *
     * @param array $data    Upsert data
     * @param array $options Options
     * @return int|\Cake\Database\StatementInterface|null Returns the number of rows changed, database statement or null.
     */
    public function bulkUpsert(array $data, array $options = [])
    {
        if (empty($data)) {
            return null;
        }
        $primaryKey = $this->_table->getPrimaryKey();
        $options += [
            'uniqueKey' => $this->getConfig('uniqueKey', (array)$primaryKey),
            'updateColumns' => $this->getConfig('updateColumns', []),
            'extra' => [],
            'returning' => [],
        ];

        $uniqueKey = $options['uniqueKey'];
        $updateColumns = $options['updateColumns'];
        $extra = $options['extra'];
        $returning = $options['returning'];
        $sequenceName = null;
        if (count($uniqueKey) === 1 && reset($uniqueKey) === $primaryKey) {
            $sequenceName = $this->_table->find()
                                         ->select(['sequence' => 'pg_get_serial_sequence(:table, :id)'])
                                         ->bind(':table', $this->_table->getTable())
                                         ->bind(':id', $primaryKey)
                                         ->first()->sequence ?? null;
        }

        $fields = array_filter(
            array_keys(reset($data)),
            function ($key) use ($updateColumns) {
                return in_array($key, $updateColumns);
            }
        );
        $fields = array_unique(array_merge($uniqueKey, $fields, array_keys($extra)));
        $updateValues = [];
        foreach ($updateColumns as $column) {
            array_push($updateValues, "{$column} = EXCLUDED.{$column}");
        }
        $conflictKey = implode(',', $uniqueKey);
        $epilog = "ON CONFLICT ({$conflictKey})";
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
            $values = array_merge($filtered, $extra);
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
