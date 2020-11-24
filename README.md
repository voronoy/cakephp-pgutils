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
    - `bool_array`
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
    $schema->setColumnType('bool_arr', 'bool_array');
    $schema->setColumnType('mac', 'macaddr');
    $schema->setColumnType('mac8', 'macaddr8');
    $schema->setColumnType('ip', 'inet');
    return $schema;
}
```

### Behaviors

- Upsert Behavior

#### Usage

##### Example 1. Configure behavior.

Articles table has columns `external_id` & `author_id` as a *composite unique key*.

```php
$this->Articles->addBehavior('Voronoy/PgUtils.Upsert', [
    'uniqueKey' => ['external_id', 'author_id'],
    'updateColumns' => ['title'],
]);
$records = [
    ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1'],
    ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
    ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 3'],
];
$this->Articles->bulkUpsert($records);
```

##### Example 2. Default configuration, pass options to method.

Articles table has columns `external_id` & `author_id` as a *composite unique key*.

```php
$this->Articles->addBehavior('Voronoy/PgUtils.Upsert');
$records = [
    ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1'],
    ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
    ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 3'],
];
$this->Articles->bulkUpsert($records, [
    'uniqueKey' => ['external_id', 'author_id'],
    'updateColumns' => ['title'],
]);
```

##### Example 3. Default configuration.

Articles table has columns `external_id` & `author_id` as a *composite primary key*.

```php
$this->Articles->addBehavior('Voronoy/PgUtils.Upsert');
$records = [
    ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1'],
    ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
    ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 3'],
];
$this->Articles->bulkUpsert($records, [
    'updateColumns' => ['title'],
    'extra' => ['modified' => date('c')]
]);
```

##### Example 4. Returning data.

Articles table has columns `external_id` & `author_id` as a *composite unique key* and `id` as *primary key*.

Upsert data and return list of `id`'s.

```php
$this->Articles->addBehavior('Voronoy/PgUtils.Upsert');
$records = [
    ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1'],
    ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
    ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 3'],
];
$statement = $this->Articles->bulkUpsert($records, [
    'updateColumns' => ['title'],
    'extra' => ['modified' => date('c')],
    'returning' => ['id']
]);
while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
    echo $row['id'];
}
```

## License

Licensed under the MIT License.
