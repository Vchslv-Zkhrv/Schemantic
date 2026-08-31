<?php

namespace Schemantic\Attribute\Group;

use Schemantic\Attribute\AttributeInterface;
use Schemantic\Exception\SchemaException;

/**
 * Abstract class for grouping attributes
 *
 * Ungrouped attributes will be joined implicitly to `default` group
 *
 * @category Library
 * @package  Schemantic\Attribute\Group
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
abstract class GroupAttribute implements AttributeInterface
{
    /**
     * @var array<class-string<SingleAttributeInterface>, SingleAttributeInterface>
     */
    protected array $single;

    /**
     * @var array<class-string<RepetitiveAttributeInterface>, RepetitiveAttributeInterface[]>
     */
    protected array $repetitive;

    /**
     * GroupAttribute constructor
     *
     * @param GroupingAttributeInterface[] ...$attributes attributes in group. No more than one of each class
     *
     * @throws SchemaException
     */
    public function __construct(GroupingAttributeInterface ...$attributes)
    {
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
     * @param GroupingAttributeInterface $attr     attribute to add
     * @param bool                       $override replace duplicate single attributes
     *
     * @return void
     */
    public function addAttribute(
        GroupingAttributeInterface $attr,
        bool $override = false,
    ): void {
        if ($attr instanceof SingleAttributeInterface) {
            if (!$override && isset($this->single[$attr::class])) {
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
