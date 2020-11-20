# CakePHP PgUtils Plugin

[![CakePHP PgUtils CI](https://github.com/voronoy/cakephp-pgutils/workflows/CakePHP%20PgUtils%20CI/badge.svg)](https://github.com/voronoy/cakephp-pgutils/actions)
[![Coverage Status](https://img.shields.io/codecov/c/gh/voronoy/cakephp-pgutils?style=flat)](https://codecov.io/gh/voronoy/cakephp-pgutils)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](https://packagist.org/packages/voronoy/cakephp-pgutils)

Provides Common PostgreSQL Types and Bulk Upsert Behavior for CakePHP ORM.

## Installation

Require the plugin through Composer:

```bash
composer require voronoy/cakephp-pgutils
```

You then need to load the plugin. You can use the console command:

```bash
bin/cake plugin load Voronoy/PgUtils
```

## What is this plugin for?

### Database Types

- Array (including multidimensional arrays):
    - `array`
    - `int_array`
    - `float_array`
- MacAddr, MacAddr8
- Inet

#### Usage

You can use the custom types by mapping the types in your Table’s `_initializeSchema()` method:

```php
protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
{
    $schema->setColumnType('txt_arr', 'array');
    $schema->setColumnType('int_arr', 'int_array');
    $schema->setColumnType('float_arr', 'float_array');
    $schema->setColumnType('mac', 'macaddr');
    $schema->setColumnType('mac8', 'macaddr8');
    $schema->setColumnType('ip', 'inet');
    return $schema;
}
```

### Behaviors

- Upsert Behavior

#### Usage

```php
$this->Articles->addBehavior('Voronoy/PgUtils.Upsert', [
    'uniqueKey' => ['external_id', 'author_id'],
    'updateColumns' => ['title', 'body'],
]);
$records = [
    ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1'],
    ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
    ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 3'],
];
$this->Articles->bulkUpsert($records);
```

## License

Licensed under the MIT License.
