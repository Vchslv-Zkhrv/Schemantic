<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if property value is not empty
 *
 * @extends BaseValidation<mixed>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class NotEmpty extends BaseValidation
{
    /**
     * NotEmpty constructor
     */
    public function __construct()
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return !empty($value);
    }

    public function getErrorMessage($value): string
    {
        return "!empty({$this->stringify($value)})";
    }
}
