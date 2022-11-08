# Upsert Behavior

A CakePHP behavior to generate bulk upsert queries.

## Usage

Attach it to your model’s Table class in its` initialize()` like so:

```php
$this->addBehavior('Voronoy/PgUtils.Upsert', $options);
```

Execute `bulkUpsert` on the table:

```php
$this->bulkUpsert($records);
```

## Options

| Key            | Default       | Description                                                                                              |
|----------------|---------------|----------------------------------------------------------------------------------------------------------|
| uniqueKey      | `$primaryKey` | List of fields which defines unique key                                                                  |
| updateColumns  | `[]`          | List of fields that will be updated on conflict. Pass `*` to set all table columns                       |
| extra          | `[]`          | Extra fields which will be appended to data                                                              |
| returning      | `[]`          | List of fields that will be returned in statement. If empty, the number of rows changed wil be returned  |

## Examples

### Example 1. Configure behavior.

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

### Example 2. Default configuration, pass options to method.

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

### Example 3. Default configuration.

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

### Example 4. Returning data.

Articles table has columns `external_id` & `author_id` as a *composite unique key* and `id` as *primary key*.

Upserts data and returns `id` & `modified` values for each row inserted or updated.

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
    'returning' => ['id', 'modified']
]);
while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
    var_dump($row['id'], $row['modified']);
}
```
