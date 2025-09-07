<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if the property value does not belong to a set
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
class NotIn extends BaseValidation
{
    /**
     * NotIn constructor
     *
     * @param mixed[] $set unacceptable values
     */
    public function __construct(public readonly array $set)
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return !in_array($value, $this->set);
    }

    public function getErrorMessage($value): string
    {
        return "{$this->stringify($value)} in {$this->stringify($this->set)}";
    }
}
