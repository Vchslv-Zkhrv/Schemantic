<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check an array property does not contains specific value
 *
 * @extends BaseValidation<array>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class HasNo extends BaseValidation
{
    /**
     * HasNo constructor
     *
     * @param mixed $value value we are looking for
     */
    public function __construct(public $value)
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return !in_array($this->value, $value);
    }

    public function getErrorMessage($value): string
    {
        return "{$this->stringify($this->value)} ∈ {$this->stringify($value)}";
    }
}
