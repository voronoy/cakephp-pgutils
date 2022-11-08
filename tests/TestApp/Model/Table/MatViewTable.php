<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestApp\Model\Table;

use Voronoy\PgUtils\ORM\MaterializedView;

class MatViewTable extends MaterializedView
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('mat_view');
    }
}
