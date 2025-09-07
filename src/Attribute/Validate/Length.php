<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check an array/string property length
 *
 * @extends BaseValidation<array|string>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class Length extends BaseValidation
{
    /**
     * Length constructor
     *
     * @param int $min minimum allowed length
     * @param int $max maximum allowed length
     */
    public function __construct(
        public readonly int $min = 0,
        public readonly int $max = PHP_INT_MAX,
    ) {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        $len = is_array($value) ? count($value) : strlen($value);
        return $len >= $this->min && $len <= $this->max;
    }

    public function getErrorMessage($value): string
    {
        $len = is_array($value) ? count($value) : strlen($value);
        return "$this->min <= len({$this->stringify($value)})=$len <= $this->max";
    }
}
