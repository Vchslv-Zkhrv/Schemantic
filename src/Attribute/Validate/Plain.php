<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if property value is a one-dimensional array
 *
 * @extends ValidateAttribute<array>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class Plain extends ValidateAttribute
{
    /**
     * Plain constructor
     */
    public function __construct()
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        foreach ($value as $item) {
            if (is_array($item)) {
                return false;
            }
        }
        return true;
    }

    public function getErrorMessage($value): string
    {
        return "{$this->stringify($value)} has nested arrays";
    }
}
