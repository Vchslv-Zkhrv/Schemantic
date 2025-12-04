<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Attribute\Validate;

class ValidatingSchema extends Schema
{
    /**
     * @param string[] $valueContains
     * @param string[] $valueHasNo
     * @param mixed[]  $valueArrayLength
     * @param mixed    $valueNotNull
     * @param mixed[]  $valuePlain
     */
    public function __construct(
        #[Validate\Contains('foo')]
        #[Validate\Contains('bar')]
        public array $valueContains,

        #[Validate\GreaterThan(17)]
        public int $valueGreaterThan,

        #[Validate\GreaterThan(new \DateTimeImmutable('2025-01-01 00:00:00'), true)]
        public \DateTimeImmutable $valueGreaterThanOrEquals,

        #[Validate\HasNo('deleted')]
        public array $valueHasNo,

        #[Validate\Length(max: 10)]
        public array $valueArrayLength,

        #[Validate\Length(min: 8)]
        public string $valueStringLength,

        #[Validate\LowerThan(new \DateTimeImmutable('today'))]
        public \DateTimeImmutable $valueLowerThan,

        #[Validate\LowerThan(100, true)]
        public float $valueLowerThanOrEquals,

        #[Validate\NotEmpty()]
        public string $valueNotEmpty,

        #[Validate\NotIn([2, 7, 10])]
        public int $valueNotIn,

        #[Validate\NotNull()]
        public $valueNotNull,

        #[Validate\OneOf(['active', 'banned', 'unpaid', 'left'])]
        public string $valueOnOf,

        #[Validate\Plain()]
        public array $valuePlain,

        #[Validate\Validator('validateValueValidate', errorMessage: 'value must be a numeric string')]
        public string $valueValidate,

        #[Validate\GreaterThan(17)]
        #[Validate\LowerThan(100)]
        #[Validate\NotIn([42, 87, 91])]
        #[Validate\Validator('validateValueManyValidations', errorMessage: 'value must be odd')]
        public int $valueManyValidations,
    ) {
    }

    public function validateValueValidate(string $value): bool
    {
        return is_numeric($value);
    }

    public function validateValueManyValidations(int $value): bool
    {
        return $value % 2 == 1;
    }
}
