<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if the property value exactly equals to
 *
 * @extends ValidateAttribute<mixed>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class Exactly extends ValidateAttribute
{
    /**
     * Exactly constructor
     *
     * @param mixed $value expected value. Comparison will be proceeded after parsing
     */
    public function __construct(public $value)
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return $value == $this->value;
    }

    public function getErrorMessage($value): string
    {
        return "{$this->stringify($value)} != {$this->stringify($this->value)}";
    }
}
