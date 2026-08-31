<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\Attribute\Group\Group;
use Schemantic\SchemaInterface;

/**
 * Use to mark that field can be filled only if another was not
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
class IfNotSet extends ValidateAttribute
{
    /**
     * IfSet constructor
     *
     * @param string $field name (unaliased) of prohibited field
     */
    public function __construct(public readonly string $field)
    {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        if (empty($value)) {
            return true;
        }

        return $schema->{$this->field} === null;
    }

    public function getErrorMessage(
        $value,
        SchemaInterface $schema,
        bool $byAlias,
        ?string $group
    ): string {
        $name = $this->field;
        if ($byAlias) {
            $aliases = $schema::getFieldAliases($this->field);
            $group = $group ?? Group::DEFAULT_GROUP_NAME;
            $name = $aliases[$group] ?? $this->field;
        }

        return "field `$name` is not empty";
    }
}
