<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use DateTimeInterface;
use Schemantic\SchemaInterface;

/**
 * Use to check if property value is lower than (or equals) to
 *
 * @extends BaseValidation<int|float|string|DateTimeInterface>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class LowerThan extends BaseValidation
{
    /**
     * LowerThan constructor
     *
     * @param int|float|string|DateTimeInterface $value      value to compare with
     * @param bool                               $orEqualsTo use `lt` or `lte` comparison
     */
    public function __construct(
        public readonly int|float|string|DateTimeInterface $value,
        public readonly bool $orEqualsTo = false,
    ) {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        if ($this->orEqualsTo) {
            return $value <= $this->value;
        } else {
            return $value < $this->value;
        }
    }

    public function getErrorMessage($value): string
    {
        $sign = $this->orEqualsTo ? '>' : '>=';
        return "{$this->stringify($value)} $sign {$this->stringify($this->value)}";
    }
}
