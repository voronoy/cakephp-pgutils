<?php

use Cake\Database\Type;
use Voronoy\PgUtils\Database\Type\ArrayType;
use Voronoy\PgUtils\Database\Type\FloatArrayType;
use Voronoy\PgUtils\Database\Type\InetType;
use Voronoy\PgUtils\Database\Type\IntArrayType;
use Voronoy\PgUtils\Database\Type\MacAddr8Type;
use Voronoy\PgUtils\Database\Type\MacAddrType;

Type::map('array', ArrayType::class);
Type::map('float_array', FloatArrayType::class);
Type::map('int_array', IntArrayType::class);
Type::map('inet', InetType::class);
Type::map('macaddr', MacAddrType::class);
Type::map('macaddr8', MacAddr8Type::class);
