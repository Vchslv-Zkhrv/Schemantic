# Schemantic

Modest PHP analogue for [Pydantic](https://github.com/pydantic/pydantic). PHP8 only.


## Features:

- Recursive parse sub-structures (and arrays of sub-structures)
- No depenencies (pure PHP)
- Based on Traits, so you can easily inject Schemantic into your project
- ORM integration (just like `model_validate` from Pydantic) with Doctrine support
- JSON parsing
- DateTime and Enum dump & parsing
- Fields aliases
- Fields validations
- Serialized string size optimization
- 3 separate datetime formats: for date, time and datetime
- `unix` datetime format support


## 100% Supported types:

1. built-in PHP type
2. DateTime and DateTimeImmutable
3. UnitEnum and BackedEnum
4. Sub-schemas
5. Arrays of types above (via `[]` sign)
6. Nullable types (via `?` sign)


## Warnings:

1. **Avoid using interfaces**. Schema will know the real class to parse into
2. **Union and generic types are not allowed** in most cases
3. When using array of sub-schemas as property type (in `@param` tag), **specify the full path to the sub-schema class** (See [example](#recursive-parsing))
4. When using `SerialSchemaTrait`, don't use `readonly` keyword


## Usage:

### Recursive parsing:

Sub-sub-schema:
```php
// src/Schemas/Category.php
class Category extends \Schemantic\Schema
{
    public function __construct(

        public readonly string $id,
        public readonly string $icon

    ) { }
}
```

Sub-schema:
```php
// src/Schemas/MenuChapter.php
class MenuChapter extends \Schemantic\Schema
{
    /**
     * It's neccessary to specify full path when using arrays of schemas
     * You don't need to write all @param tags
     * @param \App\Schemas\Category[] $categories
     */
    public function __construct(

        public readonly string $id,
        public readonly string $icon,
        public readonly array $categories

    ) { }
}
```

Another one sub-schema:
```php
// src/Schemas/WorkingHours.php
class WorkingHours implements \Schemantic\SchemaInterface
{
    // You can use 'use SchemaTrait' + 'implements SchemaInterface' instead of 'extends Schema'
    use \Schemantic\SchemaTrait;

    public function __construct(

        public \DateTimeImmutable $weekdayOpen,
        public \DateTimeImmutable $weekdayClose,
        public \DateTimeImmutable $weekendOpen,
        public \DateTimeImmutable $weekendClose

    ) { }
}
```

Main schema:
```php
class Menu extends \Schemantic\Schema
{
    /**
     * @param \App\Schema\MenuChapter[] $chapters
     */
    public function __construct(

        public array $chapters,
        public readonly WorkingHours $workingHours,
        public string $language = 'en'

    ) { }
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
            "id": "appetizersid",
            "icon": "link/to/icon",
            "categories": {
                "salads": {
                    "id": "someid1",
                    "icon" "link/to/icon"
                },
                "platters": {
                    "id": "someid2",
                    "icon": "link/to/icon"
                }
            }
        },
        "main course": {
            "id": "maincourseid",
            "icon": "link/to/icon",
            "categories": {
                "pizzas": {
                    "id": "someid3",
                    "icon": "link/to/icon"
                },
                "noodles": {
                    "id": "someid4",
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
$menuSchema = MenuSchema::fromJSON($json, dateFormat:'H:i');

// and vice versa:
$json = $menuSchema->toJSON(pretty:true, dateFormat:'H:i'); // will return the same, but with language='en'
```

---

### With ORM (like Doctrine)

Entity:
```php
// src/Entity/User.php
class User()
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
// src/Schemas/UserSchema.php
class UserSchema extends \Schemantic\Schema
{
    public function __construct(

        public readonly ?int $id,
        public readonly StatusEnum $status,
        public readonly \DateTimeImmutable $createdAt

    ) { }
}
```

Reading from object:
```php
$user = new User(StatusEnum::ACTIVE);

$userSchema = UserSchema::fromObject($user);
$responseBody = $userSchema->toJSON(skipNulls:true);
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
class UserSchema extends \Schemantic\Schema
{
    private static function validateName(string $name): bool
    {
        return strlen($name) < 100; // && some checks....
    }

    public static function getValidations(): array
    {
        rerturn [
            'birthday' => ( (new \DateTime('now'))->diff($this->birthday)->y > 17 ),
            'firstname' => self::validateName($this->firstname),
            'lastname' => self::validateName($this->lastname),
            'username' => self::validateName($this->username),
            'email' => \App\Tools\Validator::validateEmail($email)
        ];
    }

    public function __construct(
        public readonly \DateTimeImmutable $birthday,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $username,
        public readonly string $email
    )
    {
        $this->validate(true);
    }
}
```

**How it works**:
- Validations will be proceeded when using any `from...()` method or the `update()` method
- When calling `__construct`, you need to call `$this->validate(true)` manually
- When updating with property assignment (`$schema->value = $newValue`), **validations will not be proceded automatically**
- You can call `validate()` method when you neeed
