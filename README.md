# Schemantic

Recursively parsing PHP structures. Pure PHP 8.1+, no dependencies

## Installation

```
composer require schemantic/schemantic
```

## Features:

- Recursive parse sub-structures (and arrays of sub-structures)
- No depenencies (pure PHP)
- Based on Traits, so you can easily inject Schemantic into your project
- ORM integration with Doctrine support
- JSON parsing
- `DateTimeInerface` dump & parsing
- Enum dump & parsing
- Fields aliases
- Fields validations


## Supported types:

1. built-in PHP types
2. DateTimeInterface
3. UnitEnumCase and BackedEnumCase
4. Sub-schemas
5. Arrays of types above
6. Nullable types


## Usage:

### Recursive parsing:

Schemas:
```php
use Schemantic\Attribute\ArrayOf;
use Schemantic\Attribute\Alias;
use Schemantic\Attribute\DateTimeFormat;
use Schemantic\Schema;
use Schemantic\SchemaInterface;
use Schemantic\SchemaTrait;

class Category extends Schema
{
    public function __construct(
        #[Alias('categoryId')]
        public readonly string $id,

        public readonly string $icon
    ) {
    }
}


class MenuChapter extends Schema
{
    public function __construct(
        #[Alias('chapterId')]
        public readonly string $id,

        public readonly string $icon,

        #[ArrayOf(Category::class)]
        public readonly array $categories
    ) {
    }
}

#[DateTimeFormat('H:i')]
class WorkingHours implements SchemaInterface
{
    // You can use 'use SchemaTrait' + 'implements SchemaInterface' instead of 'extends Schema'
    use SchemaTrait;

    public function __construct(
        public \DateTimeImmutable $weekdayOpen,
        public \DateTimeImmutable $weekdayClose,
        public \DateTimeImmutable $weekendOpen,
        public \DateTimeImmutable $weekendClose
    ) {
    }
}

class Menu extends Schema
{
    public function __construct(
        #[ArrayOf(MenuChapter::class)]
        public array $chapters,
        public readonly WorkingHours $workingHours,
        public string $language = 'en'
    ) {
    }
}
```

Source data:
```json
{
    "workingHours": {
        "weekdayOpen": "09:00",
        "weekdayClose": "20:00",
        "weekendOpen": "12:00",
        "weekendClose": "23:00"
    },
    "chapters": {
        "appetizers": {
            "chapterId": "appetizersid",
            "icon": "link/to/icon",
            "categories": {
                "salads": {
                    "categoryId": "someid1",
                    "icon" "link/to/icon"
                },
                "platters": {
                    "categoryId": "someid2",
                    "icon": "link/to/icon"
                }
            }
        },
        "main course": {
            "chapterId": "maincourseid",
            "icon": "link/to/icon",
            "categories": {
                "pizzas": {
                    "categoryId": "someid3",
                    "icon": "link/to/icon"
                },
                "noodles": {
                    "categoryId": "someid4",
                    "icon": "link/to/icon"
                }
            }
        }
    }
}
```

Combine:
```php
// $json = ...
$menuSchema = MenuSchema::fromJSON($json);

// and vice versa:
$json = $menuSchema->toJSON(pretty: true); // will return the same, but with language='en'
```

---

### With ORM (like Doctrine)

Entity:
```php
class User
{
    private ?int $id = null;
    private string $status;
    private \DateTimeImmutable $createdAt;

    public function __construct(StatusEnum $status)
    {
        $this->status = $status->value;
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): StatusEnum
    {
        return StatusEnum::from($this->status);
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setStatus(StatusEnum $status): static
    {
        $this->status = $status->value;
    }
}
```

Schema:
```php
use Schemantic\Schema;

class UserSchema extends Schema
{
    public function __construct(
        public readonly ?int $id,
        public readonly StatusEnum $status,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
}
```

Reading from object:
```php
$user = new User(StatusEnum::ACTIVE);

$responseBody = UserSchema::fromObject($user)->toJSON(skipNulls: true);
```

Updating object:
```php
$user = $this->userRepository->find($id);
$userSchema = UserSchema::fromJSON($requestBody, validate:true);

$userSchema->updateObject($user);
```

Creating object:
```php
$userSchema = UserSchema::fromJSON($requestBody, validate:true);

$user = $userSchema->buildObject(User::class);
```
---

### Validating:

```php
use Schemantic\Schema;
use Schemantic\Attribute\Validate;

class UserSchema extends Schema
{
    public function validateBirthday(\DateTimeImmutable $birthday): bool
    {
        return (new \DateTime('now'))->diff($birthday)->y > 17;
    }

    public function __construct(
        #[Validate\Validate('validateBirthday')]
        public readonly \DateTimeImmutable $birthday,

        #[Validate\Length(max: 100)]
        public readonly string $firstname,

        #[Validate\Length(max: 100)]
        public readonly string $lastname,

        #[Validate\Length(min: 6, max: 100)]
        public readonly string $username,

        public readonly string $email,
    ) {
        $this->validate(true);
    }
}
```

**How it works**:
- Validations will be proceeded when using any `from...()` method or the `update()` method
- When calling `__construct`, you need to call `$this->validate(true)` manually
- When updating with property assignment (`$schema->value = $newValue`), **validations will not be proceded automatically**
- You can call `validate()` method when you neeed
