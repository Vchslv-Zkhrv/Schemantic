<?php

namespace Schemantic\Attribute;

use Attribute;
use Schemantic\Exception\SchemaException;

/**
 * Group of attributes
 *
 * Ungrouped attributes will be joined implicitly to `default` group
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class Group implements AttributeInterface
{
    const DEFAULT_GROUP_NAME = 'default';

    /**
     * @var array<class-string<SingleAttributeInterface>, SingleAttributeInterface>
     */
    protected array $single;

    /**
     * @var array<class-string<RepetitiveAttributeInterface>, RepetitiveAttributeInterface[]>
     */
    protected array $repetitive;

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
        $this->single = [];
        $this->repetitive = [];

        foreach ($attributes as $attr) {
            $this->addAttribute($attr);
        }
    }

    /**
     * Get single attribute by class
     *
     * @param class-string<T> $class  attribute class
     * @param bool            $strict set to `false` to allow subclasses
     *
     * @template T of SingleAttributeInterface
     *
     * @return ?T
     */
    public function getOne(string $class, bool $strict = true): ?SingleAttributeInterface
    {
        if ($strict) {
            return $this->single[$class] ?? null;
        } else {
            foreach ($this->single as $cls => $attr) {
                if (is_subclass_of($cls, $class)) {
                    return $attr;
                }
            }
            return null;
        }
    }

    /**
     * Get repetitive attributes by class
     *
     * @param class-string<T> $class  attribute class
     * @param bool            $strict set to `false` to allow subclasses
     *
     * @template T of RepetitiveAttributeInterface
     *
     * @return T[]
     */
    public function getMany(string $class, bool $strict = true): array
    {
        if ($strict) {
            return $this->repetitive[$class] ?? [];
        } else {
            $result = [];
            foreach ($this->repetitive as $cls => $attrs) {
                if (is_subclass_of($cls, $class)) {
                    $result = array_merge($result, $attrs);
                }
            }
            return $result;
        }
    }

    /**
     * Add attribute to group
     *
     * @param GroupingAttributeInterface $attr attribute to add
     *
     * @return void
     */
    public function addAttribute(GroupingAttributeInterface $attr): void
    {
        if ($attr instanceof SingleAttributeInterface) {
            if (isset($this->single[$attr::class])) {
                throw new SchemaException("Cannot group repetative attributes of class " . $attr::class);
            }

            $this->single[$attr::class] = $attr;
        } elseif ($attr instanceof RepetitiveAttributeInterface) {
            $this->repetitive[$attr::class][] = $attr;
        } else {
            throw new SchemaException(
                "Cannot add attribute of class " . $attr::class . 
                ". Each grouping attribute must implement " .
                "either SingleAttributeInterface or RepetitiveAttributeInterface"
            );
        }
    }

    /**
     * Get all single attributes in group
     *
     * @return array<class-string<SingleAttributeInterface>, SingleAttributeInterface>
     */
    public function allSingle(): array
    {
        return $this->single;
    }

    /**
     * Get all repetitive attributes in group
     *
     * @return array<class-string<RepetitiveAttributeInterface>, RepetitiveAttributeInterface[]>
     */
    public function allRepetitive(): array
    {
        return $this->repetitive;
    }

    /**
     * Create deafult group
     *
     * @return static
     */
    public static function default(): static
    {
        return new static(static::DEFAULT_GROUP_NAME);
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
     * @param Group   $group     group
     * @param Group[] ...$groups groups
     *
     * @return static new group
     */
    public static function merge(
        Group $group,
        Group ...$groups,
    ): static {
        $merged = new Group($group->name);

        foreach ($groups as $g) {
            if ($g->name != $merged->name) {
                throw new SchemaException("Cannot merge groups with different names '$merged->name' and '$g->name'");
            }
            foreach ($g->allSingle() as $attr) {
                $merged->addAttribute($attr);
            }
            foreach ($g->allRepetitive() as $attrs) {
                foreach ($attrs as $attr) {
                    $merged->addAttribute($attr);
                }
            }
        }

        return $merged;
    }

    /**
     * Check that group has any attribute implementing that class
     *
     * @param class-string $class class or interface name
     *
     * @return bool
     */
    public function has(string $class): bool
    {
        foreach (array_keys($this->single) as $key) {
            if (is_subclass_of($key, $class)) {
                return true;
            }
        }
        foreach (array_keys($this->repetitive) as $key) {
            if (is_subclass_of($key, $class)) {
                return true;
            }
        }
        return false;
    }
}
