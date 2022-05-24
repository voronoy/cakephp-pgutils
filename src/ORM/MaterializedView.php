<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\ORM;

use Cake\Database\Schema\CachedCollection;
use Cake\Database\Schema\CollectionInterface as SchemaCollectionInterface;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;
use Voronoy\PgUtils\Database\Schema\MaterializedViewCollection;

class MaterializedView extends Table
{
    /**
     * @var \Cake\Database\Schema\CollectionInterface[]
     */
    protected static $_schemaCollections = [];

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        if ($this->_schema === null) {
            $this->_schema = $this->_initializeSchema(
                $this->getSchemaCollection()->describe($this->getTable())
            );
        }

        return $this->_schema;
    }

    /**
     * Aborts the save operation.
     *
     * @return false
     */
    public function beforeSave(): bool
    {
        return false;
    }

    /**
     * Aborts the delete operation.
     *
     * @return false
     */
    public function beforeDelete(): bool
    {
        return false;
    }

    /**
     * Refresh Materialized View.
     *
     * @return void
     */
    public function refresh(): void
    {
        $connection = $this->getConnection();
        $name = $connection->quoteIdentifier($this->getTable());
        $connection->execute(sprintf('REFRESH MATERIALIZED VIEW %s', $name))->closeCursor();
    }

    /**
     * Refresh Materialized View with no data.
     *
     * @return void
     */
    public function truncate(): void
    {
        $connection = $this->getConnection();
        $name = $connection->quoteIdentifier($this->getTable());
        $connection->execute(sprintf('REFRESH MATERIALIZED VIEW %s WITH NO DATA', $name))->closeCursor();
    }

    /**
     * Gets a Schema\Collection object.
     *
     * @return \Cake\Database\Schema\CollectionInterface
     */
    protected function getSchemaCollection(): SchemaCollectionInterface
    {
        $connection = $this->getConnection();
        $name = $connection->configName();
        if (!empty(static::$_schemaCollections[$name])) {
            return static::$_schemaCollections[$name];
        }

        $config = $connection->config();
        if (!empty($config['cacheMetadata'])) {
            return static::$_schemaCollections[$name] = new CachedCollection(
                new MaterializedViewCollection($connection),
                empty($config['cacheKeyPrefix']) ? $name : $config['cacheKeyPrefix'],
                $connection->getCacher()
            );
        }

        return static::$_schemaCollections[$name] = new MaterializedViewCollection($connection);
    }
}
