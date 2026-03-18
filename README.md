# Schemantic

Recursively parsing PHP structures. Pure PHP 8.1+, no dependencies

## Installation

```
composer require schemantic/schemantic
```

## Simple example


Schemas:

```php
use Schemantic\Attribute\ArrayOf;
use Schemantic\Attribute\Validate;
use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class Tag extends Schema
{
    public function __construct(
        #[Validate\GreaterThan(0)]
        public readonly int $id,

        #[Validate\NotEmpty]
        #[Alias('name')]
        public readonly stirng $title,

        #[Validate\Validator(ValidateHelper::class, 'validateColor')]
        public readonly ?string $color,
    ) {
    }
}


class Product extends Schema
{
    public function __construct(
        #[Validate\GreaterThan(0)]
        public readonly int $id,

        public readonly string $name,

        #[Timestamp]
        public readonly \DateTimeImmutable $createdAt,

        #[Timestamp]
        public readonly ?\DateTimeImmutable $deletedAt = null,

        #[Validate\GreaterThan(0)]
        public readonly ?float $price = null,

        #[ArrayOf(Tag::class)]
        #[Validate\Length(max: 3)]
        public readonly array $tags = [],
    ) {
    }
}
```


Data to read:

```json
{
    "id": 123456789,
    "name": "test-product",
    "createdAt": 1771852144,
    "price": 12.3,
    "tags": [
        {
            "id": 1,
            "name": "tag1",
            "color": "#ffd900"
        },
        {
            "id": 2,
            "name": "tag2",
            "color": null
        }
    ]
}
```


Usage:

```php
$raw = '{...}';  // the JSON from above

// easily switch between different formats
$responseData = Product::fromJSON($raw)->toArray(byAlias: true, dump: true, skipNulls: true);
```

```php
$data = '[{...}, {...}]';  // array of JSONs from above

// read JSON as Product[], and exclude rows that do not pass validation
$rows = Product::fromJSONMultiple($data, validate: 'exclude');
```

```php
$model = new \App\Models\Product();  // Laravel model / Doctrine Entity

// Update any object fields with an direct assignment and calling setters (::setName(), setPublic()). Virtual setters included
$schema = ...;
$schema->updateObject($model);
```

```php
$model = ...;  // Laravel model / Doctrine Entity

// Read any object fields with direct access and calling getters (::getName(), ::getPublic()). Virtual getter included
$schema = Product::fromObject($model);
```

```php
$schema = ...;

// Pass __construct params & set other properties after construction if needed
$model = Product::buildObject(\App\Models\Product::class);
```



## Features:

- __Recursive__ sub-structures parsing (and arrays of sub-structures)
- Custom field __validation__, __parsing__ and __dumping__ via attributes
- Pre-defined fields validations
- Fields aliases
- ORM integration
- JSON parsing
- Enum dump & parsing


## Supported types:

1. built-in PHP types
2. DateTimeInterface
3. UnitEnum and BackedEnum
4. Sub-schemas
5. Arrays of types above
6. Nullable types
7. Union types
