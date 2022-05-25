<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

class IntArrayType extends ArrayType
{
    /**
     * @var string
     */
    protected $type = 'int';
}
