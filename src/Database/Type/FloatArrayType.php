<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

class FloatArrayType extends ArrayType
{
    /**
     * @var string
     */
    protected string $type = 'float';
}
