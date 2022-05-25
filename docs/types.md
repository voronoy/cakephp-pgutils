# Database Types

The plugin provides the following PostgreSQL types:

- Array Types (including multidimensional arrays):
  - `array`
  - `int_array`
  - `float_array`
  - `bool_array`
- Network Address Types:
  - `macaddr`
  - `macaddr8`
  - `inet`
- PostGIS Geometry/Geography Types:
  - `geo_point`


## Usage

In your Table’s `_initializeSchema()` method add the following:

```php
protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
{
    // Array Types
    $schema->setColumnType('txt_arr_field', 'array');
    $schema->setColumnType('int_arr_field', 'int_array');
    $schema->setColumnType('float_arr_field', 'float_array');
    $schema->setColumnType('bool_arr_field', 'bool_array');
    // Network Address Types
    $schema->setColumnType('mac_field', 'macaddr');
    $schema->setColumnType('mac8_field', 'macaddr8');
    $schema->setColumnType('ip_field', 'inet');
    // PostGIS Geometry/Geography Types
    $schema->setColumnType('pt_field', 'geo_point');

    return $schema;
}
```
