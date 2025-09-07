<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if property value is not null
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
class NotNull extends BaseValidation
{
    /**
     * NotNull constructor
     */
    public function __construct()
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return $value !== null;
    }

    public function getErrorMessage($value): string
    {
        return "NULL";
    }
}
