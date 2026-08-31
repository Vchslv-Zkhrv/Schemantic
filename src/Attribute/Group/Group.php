<?php

namespace Schemantic\Attribute\Group;

use Attribute;
use Schemantic\Exception\SchemaException;

/**
 * Group of attributes
 *
 * Ungrouped attributes will be joined implicitly to `default` group
 *
 * @category Library
 * @package  Schemantic\Attribute\Group
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class Group extends GroupAttribute
{
    const DEFAULT_GROUP_NAME = 'default';

    /**
     * Group constructor
     *
     * @param string                       $name          group name. Groups with same name will be merged
     * @param GroupingAttributeInterface[] ...$attributes attributes in group. No more than one of each class
     *
     * @throws SchemaException
     */
    public function __construct(
        public readonly string $name,
        GroupingAttributeInterface ...$attributes
    ) {
        parent::__construct(...$attributes);
    }

    /**
     * Create deafult group
     *
     * @param GroupingAttributeInterface[] ...$attributes attributes in group. No more than one of each class
     *
     * @return static
     */
    public static function default(GroupingAttributeInterface ...$attributes): static
    {
        return new static(static::DEFAULT_GROUP_NAME, ...$attributes);
    }

    /**
     * Check if grop is default
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->name == static::DEFAULT_GROUP_NAME;
    }

    /**
     * Merge two groups
     *
     * @param Group $group        group
     * @param Group $anotherGroup another group with the same name
     * @param bool  $override     replace duplicate single attributes
     *
     * @return static new group
     */
    public static function merge(
        Group $group,
        GroupAttribute $anotherGroup,
        bool $override = false,
    ): static {
        $merged = clone $group;

        if ($anotherGroup instanceof Group && $anotherGroup->name != $merged->name) {
            throw new SchemaException(
                "Cannot merge groups with different names '$merged->name' and '$anotherGroup->name'"
            );
        }
        foreach ($anotherGroup->allSingle() as $attr) {
            $merged->addAttribute($attr, override: $override);
        }
        foreach ($anotherGroup->allRepetitive() as $attrs) {
            foreach ($attrs as $attr) {
                $merged->addAttribute($attr);
            }
        }

        return $merged;
    }
}
