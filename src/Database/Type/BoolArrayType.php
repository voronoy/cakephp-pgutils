<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

class BoolArrayType extends ArrayType
{
    /**
     * @var string
     */
    protected $type = 'bool';
}
