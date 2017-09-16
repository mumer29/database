## Schema

This class is a utility to modify database and table schemas. 

```php
$schema = new \Odan\Database\Schema($db);
```

### Get Current database

```php
$database = $schema->getDatabase();
```

### Change database

```php
$schema->setDatabase('my_database');
```

Todo: Write more documentation
