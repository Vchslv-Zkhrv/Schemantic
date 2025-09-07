<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to check if the property value belongs to a set
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
class OneOf extends BaseValidation
{
    /**
     * OneOf constructor
     *
     * @param mixed[] $set acceptable values
     */
    public function __construct(public readonly array $set)
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        return in_array($value, $this->set);
    }

    public function getErrorMessage($value): string
    {
        return "{$this->stringify($value)} ∉ {$this->stringify($this->set)}";
    }
}
