<?php

namespace Schemantic;

use Schemantic\Attribute\Alias;
use Schemantic\Attribute\ArrayOf;
use Schemantic\Attribute\AttributeInterface;
use Schemantic\Attribute\DateTimeFormat;
use Schemantic\Attribute\Dump\BaseDumpInterface;
use Schemantic\Attribute\Dump\DumpInterface;
use Schemantic\Attribute\Group;
use Schemantic\Attribute\Parse\ParseInterface;
use Schemantic\Attribute\Propagate;
use Schemantic\Attribute\Validate\ValidateAttribute;
use Schemantic\Exception\DateParsingException;
use Schemantic\Exception\SchemaException;
use Schemantic\Exception\ParsingException;
use Schemantic\Exception\ValidationException;

/**
 * Recursively parsing structure trait.
 *
 * Supports built-in types, enums, datetime, nested structures and arrays of them all.
 * Can read arrays, jsons, objects, query params, env variables and more.
 *
 * Combine with SchemaInterface to use Schemantic with your old DTOs
 *
 * @category Library
 * @package  Schemantic
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[DateTimeFormat('Y-m-d\TH:i:s')]
trait SchemaTrait
{
    /**
     * @param ?string $group group of attributes
     *
     * @return Group
     */
    private static function _getSchemaAttributes(?string $group = null): Group
    {
        $group = $group ?? Group::DEFAULT_GROUP_NAME;
        $currentGroup = new Group($group);
        $allGroups = [Group::DEFAULT_GROUP_NAME,];

        foreach ((new \ReflectionClass(static::class))->getAttributes() as $attr) {
            $attr = $attr->newInstance();

            if (!is_subclass_of($attr::class, AttributeInterface::class)) {
                continue;
            }

            if ($attr instanceof Group) {
                $allGroups[] = $attr->name;
                if ($attr->name == $group) {
                    $currentGroup = Group::merge($currentGroup, $attr);
                }
            } elseif ($group == Group::DEFAULT_GROUP_NAME) {
                $currentGroup->addAttribute($attr);
            }
        }

        if (!in_array($group, $allGroups)) {
            return new Group($group);
        }

        return $currentGroup;
    }

    /**
     * @param bool    $byAlias alias result array keys or not
     * @param ?string $group   group of attributes
     *
     * @return array<string,Group> field: attributes
     */
    private static function _getPropertiesAttributes(
        bool $byAlias = false,
        ?string $group = null,
    ): array {
        $group = $group ?? Group::DEFAULT_GROUP_NAME;
        $allGroups = [Group::DEFAULT_GROUP_NAME,];

        $params = [];
        $reflectionParams = (new \ReflectionMethod(static::class, '__construct'))->getParameters();

        foreach ($reflectionParams as $param) {
            $paramAttrs = [];
            $paramName = $param->name;
            $paramGroup = new Group($group);

            foreach ($param->getAttributes() as $attr) {
                $attr = $attr->newInstance();

                if (!is_subclass_of($attr::class, AttributeInterface::class)) {
                    continue;
                }

                if ($attr instanceof Group) {
                    $allGroups[] = $attr->name;
                    if ($attr->name == $group) {
                        $paramGroup =  Group::merge($paramGroup, $attr);
                        if ($byAlias && ($alias = $attr->getOne(Alias::class))) {
                            $paramName = $alias->alias;
                        }
                    }
                } elseif ($group == Group::DEFAULT_GROUP_NAME) {
                    if ($byAlias & $attr instanceof Alias) {
                        $paramName = $attr->alias;
                    }
                    $paramGroup->addAttribute($attr);
                }
            }

            $params[$paramName] = $paramGroup;
        }

        if (!in_array($group, $allGroups)) {
            throw new SchemaException("No such group: '$group'");
        }

        return $params;
    }

    /**
     * @param ?string $group group of attributes
     *
     * @return array<string,string> `{unaliased: alias}`
     */
    private static function _getAliases(?string $group): array
    {
        $aliases = [];
        $propertiesAttributes = self::_getPropertiesAttributes(byAlias: false, group: $group);
        foreach ($propertiesAttributes as $name => $attributes) {
            if ($alias = $attributes->getOne(Alias::class)) {
                $aliases[$name] = $alias->alias;
            }
        }

        return $aliases;
    }

    /**
     * Recursively parse subarrays as subschemas
     *
     * @param array<string,mixed> $raw      source array
     * @param bool                $byAlias  apply aliases
     * @param bool                $validate process validation after parsing
     * @param bool                $parse    parse strings/ints as DateTimes/enums
     * @param ?string             $group    group of attributes
     *
     * @return array<string, mixed>
     *
     * @throws SchemaException
     */
    private static function _parseRecursive(
        array $raw,
        bool $byAlias,
        bool $validate,
        bool $parse,
        ?string $group,
    ): array {
        $schemaAttributes = self::_getSchemaAttributes(group: $group);
        $propertiesAttributes = self::_getPropertiesAttributes(byAlias: false, group: $group);

        $propagated = [];
        $params = (new \ReflectionMethod(static::class, '__construct'))->getParameters();
        foreach ($params as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $raw)) {
                $value = $raw[$name];
            } elseif ($param->isOptional()) {
                $value = $param->getDefaultValue();
            } else {
                throw new SchemaException("No value provided for required field `$name`");
            }

            $attributes = $propertiesAttributes[$name];
            $arrayOfAttribute = $attributes->getOne(ArrayOf::class);

            $propagateAttribute = $attributes->getOne(Propagate::class);
            if ($propagateAttribute) {
                $propagated[$name] = $value;
            }

            $parseAttribute = $attributes->getOne(ParseInterface::class, strict: false);
            if ($parse && $parseAttribute) {
                $raw[$name] = $parseAttribute->parse($value, static::class);
                continue;
            }

            $asType = $param->getType();
            if ($asType instanceof \ReflectionUnionType) {
                $types = $asType->getTypes();
            } else {
                $types = [$asType];
            }

            foreach ($types as $i => $type) {
                try {
                    $strType = (string)$type;
                    $strType = str_replace('?', '', $strType);

                    $arrayOf = false;
                    if ($arrayOfAttribute && $strType === 'array' || mb_strpos($strType, '[]') !== false) {
                        $arrayOf = true;
                        $strType = $arrayOfAttribute->class;
                    }

                    if (($type === null || $type->isBuiltin()) && !$arrayOf) {
                        break;
                    }

                    if (!$arrayOf && !self::_isSchema($type)) {
                        if ($parse && $value !== null) {
                            $raw[$name] = self::_parse(
                                value: $value,
                                name: $name,
                                type: $type,
                                propertyAttributes: $attributes,
                                schemaAttributes: $schemaAttributes,
                            );
                        }
                        break;
                    }

                    if ($type->allowsNull()) {
                        if (!array_key_exists($name, $raw)) {
                            $value = $param->getDefaultValue();
                            break;
                        } elseif ($value === null) {
                            break;
                        }
                    }

                    $schemaValues = $value ?? null;
                    if ($schemaValues instanceof SchemaInterface) {
                        $schemaValues = $schemaValues->toArray(
                            skipNulls: false,
                            byAlias: false,
                            dump: false,
                            group: $group,
                        );
                    }

                    if (!is_array($schemaValues)) {
                        $cls = gettype($schemaValues);
                        throw new SchemaException("$type: Parameter $name must be an array or schema, $cls given");
                    }

                    try {
                        if ($arrayOf) {
                            foreach ($schemaValues as $key => $values) {
                                $raw[$name][$key] = $strType::fromArray(
                                    raw: array_merge($propagated, $values),
                                    byAlias: $byAlias,
                                    validate: $validate,
                                    parse: $parse,
                                    group: $group,
                                );
                            }
                        } else {
                            $raw[$name] = $strType::fromArray(
                                raw: array_merge($propagated, $schemaValues),
                                byAlias: $byAlias,
                                validate: $validate,
                                parse: $parse,
                                group: $group,
                            );
                        }
                        break;
                    } catch (SchemaException $se) {
                        throw $se;
                    } catch (\Throwable $e) {
                        // if an unexpected error occurred, raise SchemaException with previous=e
                        throw new SchemaException("$type: {$e->getMessage()}", $e->getCode(), $e);
                    }
                } catch (\Throwable $e) {
                    if ($i+1 == count($types)) {
                        throw $e;
                    }
                }
            }
        }

        $names = array_column($params, 'name');
        foreach ($raw as $key => $value) {
            if (!in_array($key, $names)) {
                unset($raw[$key]);
            }
        }

        return $raw;
    }

    /**
     * Parses JSON into Schema
     *
     * @param string              $json     JSON string
     * @param array<string,mixed> $extra    additional fields. Can override JSON fields.
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
     * @param ?string             $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     * @throws \JsonException
     */
    public static function fromJSON(
        string $json,
        array $extra = [],
        bool $byAlias = true,
        bool $validate = true,
        ?string $group = null
    ): static {
        $raw = json_decode($json, true, flags:JSON_THROW_ON_ERROR);
        return static::fromArray(
            raw: array_merge($raw, $extra),
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            group: $group,
        );
    }

    /**
     * Reads values from environment variables
     *
     * @param bool                $byAlias  use aliases to parse or not
     * @param array<string,mixed> $extra    unaliased. Can override env params.
     * @param bool                $validate process validations after parsing or not
     * @param ?string             $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function fromEnv(
        bool $byAlias = true,
        array $extra = [],
        bool $validate = true,
        ?string $group = null,
    ): static {
        $keys = self::getContructParams(byAlias: false, group: $group);
        if ($byAlias) {
            $keys = self::_applyAlias(names: $keys, group: $group);
        }

        $values = array_map(
            function (string $n) {
                $env = getenv($n);
                return $env == false ? null : $env;
            },
            $keys
        );

        return static::fromArray(
            raw: array_merge($values, $extra),
            validate: $validate,
            byAlias: $byAlias,
            parse: true,
            group: $group,
        );
    }

    /**
     * Reads both object public properties (including virtual) and gettters to build itslef
     *
     * @param object              $object   object to parse
     * @param array<string,mixed> $extra    Additional fields (not aliased). Can override object fields
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
     * @param ?string             $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     */
    public static function fromObject(
        object $object,
        array $extra = [],
        bool $byAlias = false,
        bool $validate = true,
        ?string $group = null,
    ): static {
        $names = self::getContructParams(byAlias: $byAlias, group: $group);
        $values = [];

        // fill from properties
        foreach (get_object_vars($object) as $k => $v) {
            if (in_array($k, $names)) {
                $values[$k] = $v;
            }
        }

        // fill from getters
        $getters = array_filter(
            get_class_methods($object),
            fn (string $m) => mb_substr($m, 0, 3) === 'get'
        );

        foreach ($getters as $getter) {
            $name = lcfirst(mb_substr($getter, 3));
            if (in_array($name, $names)) {
                $values[$name] = call_user_func([$object, $getter]);
                continue;
            }
            $name = 'is' . ucfirst($name);
            if (in_array($name, $names)) {
                $values[$name] = call_user_func([$object, $getter]);
            }
        }

        // fill from extra
        $values = array_merge($values, $extra);

        // if some fields missing, try to use __get method (maybe there are virtual properties exists)
        foreach (array_diff($names, array_keys($values)) as $name) {
            $values[$name] = $object->$name;
        }

        return static::fromArray(
            raw: $values,
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            group: $group,
        );
    }

    /**
     * Create object from class & fill it.
     * Uses construct params, public fields (including virtual) and setter methods
     *
     * @param class-string<T>     $class   class to build from
     * @param array<string,mixed> $extra   Additional fields (not aliased). Can override env params.
     * @param bool                $byAlias use aliases to parse or not
     * @param ?string             $group   group of attributes
     *
     * @template T
     *
     * @return T
     *
     * @throws \Exception some exceptions that can be raised by object `__construct` method
     * @throws \ArgumentCountError if there are not enough fields to call object `__construct` method
     */
    public function buildObject(
        string $class,
        array $extra = [],
        bool $byAlias = false,
        ?string $group = null,
    ): object {
        $fields = array_merge($this->getFields(byAlias: $byAlias, group: $group), $extra);

        if (in_array('__construct', get_class_methods($class))) {
            $constructParams = array_map(
                function (\ReflectionParameter $p) use ($fields) {
                    $name = $p->name;
                    $val = $fields[$name];
                    unset($fields[$name]);
                    return $val;
                },
                (new \ReflectionMethod($class, '__construct'))->getParameters()
            );
        } else {
            $constructParams = [ ];
        }

        // create with __construct params
        $object = new $class(...$constructParams);

        // fill with setters
        $setters = array_filter(
            get_class_methods($object),
            fn (string $m) => mb_substr($m, 0, 3) === 'set'
        );

        foreach ($setters as $setter) {
            $name = lcfirst(mb_substr($setter, 3));

            if (array_key_exists($name, $fields)) {
                if (is_array($fields[$name])) {
                    call_user_func([$object, $setter], ...$fields[$name]);
                } else {
                    call_user_func([$object, $setter], $fields[$name]);
                }
                unset($fields[$name]);
                continue;
            }

            $name = 'is' . ucfirst($name);

            if (array_key_exists($name, $fields)) {
                if (is_array($fields[$name])) {
                    call_user_func([$object, $setter], ...$fields[$name]);
                } else {
                    call_user_func([$object, $setter], $fields[$name]);
                }
                unset($fields[$name]);
            }
        }

        // if all fields provided
        if (!$fields) {
            return $object;
        }

        // fill with public properties
        foreach (get_object_vars($object) as $key => $val) {
            if (array_key_exists($key, $fields)) {
                $object->$key = $fields[$key];
                unset($fields[$key]);
            }
        }

        // if all fields provided
        if (!$fields) {
            return $object;
        }

        // fill with virtual properies
        foreach ($fields as $key => $val) {
            $object->$key = $val;
        }

        return $object;
    }

    /**
     * Updates object with own fields.
     * Uses both properties (including virtual) and setter methods
     *
     * @param object              $object  object to update
     * @param array<string,mixed> $extra   Additional fields (not aliased). Can override env params
     * @param bool                $byAlias use aliases to parse or not
     * @param ?string             $group   group of attributes
     *
     * @return void
     *
     * @throws \Exception some exceptions that can be raised by object `set...` or `is...` methods
     */
    public function updateObject(
        object &$object,
        array $extra = [],
        bool $byAlias = false,
        ?string $group = null,
    ): void {
        $fields = array_merge($this->getFields(byAlias: $byAlias, group: $group), $extra);

        $setters = array_filter(
            get_class_methods($object),
            fn (string $m) => mb_substr($m, 0, 3) === 'set'
        );

        // update with setters
        foreach ($setters as $setter) {
            $name = lcfirst(mb_substr($setter, 3));

            if (array_key_exists($name, $fields) || array_key_exists("is$name", $fields)) {
                if (is_array($fields[$name])) {
                    call_user_func([$object, $setter], ...$fields[$name]);
                } else {
                    call_user_func([$object, $setter], $fields[$name]);
                }

                unset($fields[$name]);
                continue;
            }

            $name = 'is' . ucfirst($name);

            if (array_key_exists($name, $fields) || array_key_exists("is$name", $fields)) {
                if (is_array($fields[$name])) {
                    call_user_func([$object, $setter], ...$fields[$name]);
                } else {
                    call_user_func([$object, $setter], $fields[$name]);
                }

                unset($fields[$name]);
            }
        }

        // if all fields provided
        if (!$fields) {
            return;
        }

        // update with public properties
        foreach (get_object_vars($object) as $key => $val) {
            if (array_key_exists($key, $fields)) {
                $object->$key = $fields[$key];
                unset($fields[$key]);
            }
        }

        // if all fields provided
        if (!$fields) {
            return;
        }

        // update with virtual properies
        foreach ($fields as $key => $val) {
            $object->$key = $val;
        }

        return;
    }

    /**
     * Parses array as Schema
     *
     * @param array<stirng|int,mixed> $raw      both associative and non-associative arrays allowed
     * @param bool                    $byAlias  use aliases to parse or not
     * @param bool                    $validate process validations after parsing or not
     * @param bool                    $parse    parse strings into DateTimeInterface/Enum or not
     * @param ?string                 $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function fromArray(
        array $raw,
        bool $byAlias = false,
        bool $validate = true,
        bool $parse = false,
        ?string $group = null,
    ): static {
        if (array_is_list($raw)) {
            $params = static::getContructParams(byAlias: false, group: $group);

            if (count($raw) < count($params)) {
                $params = array_slice($params, 0, count($raw));
            } elseif (count($raw) > count($params)) {
                $raw = array_slice($raw, 0, count($params));
            }

            $raw = array_combine($params, $raw);
            $byAlias = false;
        }

        if ($byAlias) {
            $aliases = array_flip(self::_getAliases(group: $group));
            foreach ($raw as $k => $v) {
                if (array_key_exists($k, $aliases)) {
                    $raw[$aliases[$k]] = $raw[$k];
                    unset($raw[$k]);
                }
            }
        }

        try {
            $values = self::_parseRecursive(
                raw: $raw,
                byAlias: $byAlias,
                validate: $validate,
                parse: $parse,
                group: $group,
            );
            $schema = new static(...$values); // @phpstan-ignore-line
        } catch (SchemaException $se) {
            throw $se;
        } catch (\Throwable $e) {
            throw new SchemaException("An error occurred: '{$e->getMessage()}'", $e->getCode(), $e);
        }

        if ($validate) {
            $schema->validate(throw: true, stopOnFail: false, group: $group);
        }

        return $schema;
    }

    /**
     * Dumps schema into JSON
     *
     * @param bool    $pretty    pretty print + unescaped slashes + unescaped unicode
     * @param bool    $skipNulls remove `null` fields from JSON
     * @param bool    $byAlias   use aliases to parse or not
     * @param ?string $group     group of attributes
     *
     * @return string
     */
    public function toJSON(
        bool $pretty = false,
        bool $skipNulls = false,
        bool $byAlias = true,
        ?string $group = null,
    ): string {
        $dump = $this->toArray(
            skipNulls: $skipNulls,
            byAlias: $byAlias,
            dump: true,
            group: $group,
        );

        if ($pretty) {
            return json_encode($dump, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($dump);
        }
    }

    /**
     * Build query string or form-data body
     *
     * @param bool     $skipNulls remove `null` fields from query string
     * @param bool     $byAlias   apply field aliases
     * @param string[] $omit      fields names to omit
     * @param ?string  $group     group of attributes
     *
     * @return string
     */
    public function toQuery(
        bool $skipNulls = true,
        bool $byAlias = true,
        array $omit = [],
        ?string $group = null,
    ): string {
        return http_build_query(
            array_diff_key(
                $this->toArray(
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: true,
                    group: $group,
                ),
                array_flip($omit)
            )
        );
    }

    /**
     * Parse query string or form-data body
     *
     * @param string              $query    query string or form-data content
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
     * @param array<string,mixed> $extra    additional fields. Can override query params
     * @param ?string             $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function fromQuery(
        string $query,
        bool $byAlias = true,
        bool $validate = true,
        array $extra = [],
        ?string $group = null,
    ): static {
        $array = [];
        mb_parse_str($query, $array);
        return static::fromArray(
            raw: array_merge($array, $extra),
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            group: $group,
        );
    }

    /**
     * Recursively dump subschemas into subarrays
     *
     * @param array   $fields    fields as-is
     * @param bool    $skipNulls skip null values
     * @param bool    $byAlias   apply aliases
     * @param bool    $dump      convert \DateTimeInterface and Enums as strings
     * @param ?string $group     group of attributes
     *
     * @return array<string, mixed>
     */
    private static function _dumpRecursive(
        array $fields,
        bool $skipNulls,
        bool $byAlias,
        bool $dump,
        ?string $group,
    ): array {
        $array = [];
        $schemaAttributes = self::_getSchemaAttributes(group: $group);
        $propertiesAttributes = self::_getPropertiesAttributes(byAlias: false, group: $group);

        foreach ($fields as $key => $value) {
            $propertyAttributes = $propertiesAttributes[$key] ?? null;
            // phpcs:disable
            if (
                $dump
                && (
                    $value instanceof \DateTimeInterface
                    || $value instanceof \UnitEnum
                    || $propertyAttributes?->has(BaseDumpInterface::class)
                )
            ) {
            // phpcs:enable
                $array[$key] = self::_dump(
                    value: $value,
                    schemaAttributes: $schemaAttributes,
                    propertyAttributes: $propertyAttributes,
                );
            } elseif ($value instanceof SchemaInterface) {
                $array[$key] = $value->toArray(
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: $dump,
                    group: $group,
                );
            } elseif (is_array($value)) {
                $array[$key] = self::_dumpRecursive(
                    fields: $value,
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: $dump,
                    group: $group,
                );
            } else {
                if ($skipNulls && $value === null) {
                    continue;
                }
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Replace keys with aliases in array
     *
     * @param array<int,string> $names unaliased array
     * @param ?string           $group group of attributes
     *
     * @return array<int,string>
     */
    private static function _applyAlias(
        array $names,
        ?string $group,
    ): array {
        $aliases = self::_getAliases(group: $group);
        foreach ($names as $id => $name) {
            if (array_key_exists($name, $aliases)) {
                $names[$id] = $aliases[$name];
            }
        }
        return $names;
    }

    /**
     * Get `__construct` params names
     *
     * @param bool    $byAlias apply field aliases
     * @param ?string $group   group of attributes
     *
     * @return array<int,string>
     */
    public static function getContructParams(
        bool $byAlias = false,
        ?string $group = null,
    ): array {
        $names = array_map(
            fn (\ReflectionParameter $p) => $p->name,
            (new \ReflectionMethod(static::class, '__construct'))->getParameters()
        );

        if ($byAlias) {
            return self::_applyAlias(names: $names, group: $group);
        } else {
            return $names;
        }
    }

    /**
     * Returns fields as associative array as-is
     *
     * @param bool    $byAlias apply field aliases
     * @param ?string $group   group of attributes
     *
     * @return array<string,mixed>
     */
    public function getFields(
        bool $byAlias = false,
        ?string $group = null,
    ): array {
        $keys = $params = self::getContructParams(byAlias: false, group: $group);

        if ($byAlias) {
            $keys = self::_applyAlias(names: $keys, group: $group);
        }

        return array_combine(
            $keys,
            array_map(
                fn (string $n) => $this->$n,
                $params
            )
        );
    }

    /**
     * Returns fields as array. Dumps subschemas into subarrays
     *
     * @param bool    $skipNulls remove `null` fields from array
     * @param bool    $byAlias   apply field aliases
     * @param bool    $dump      convert dates and enums to strings
     * @param ?string $group     group of attributes
     *
     * @return array<string,mixed>
     */
    public function toArray(
        bool $skipNulls = false,
        bool $byAlias = false,
        bool $dump = false,
        ?string $group = null,
    ): array {
        $result = self::_dumpRecursive(
            fields: $this->getFields($byAlias),
            skipNulls: $skipNulls,
            byAlias: $byAlias,
            dump: $dump,
            group: $group,
        );

        if ($byAlias) {
            foreach (self::_getAliases(group: $group) as $old => $new) {
                if (array_key_exists($old, $result)) {
                    $result[$new] = $result[$old];
                    unset($result[$old]);
                }
            }
        }

        return $result;
    }

    /**
     * Writes php-valid cache
     *
     * @param string  $file  file path
     * @param ?string $group group of attributes
     *
     * @return void
     */
    public function writeCache(
        string $file,
        ?string $group = null,
    ): void {
        $export = var_export(
            $this->toArray(
                skipNulls: false,
                byAlias: false,
                dump: false,
                group: $group,
            ),
            true
        );

        file_put_contents($file, "<?php\n\nreturn $export;");
    }

    /**
     * Reads php cache (builded with writeCache or var_export)
     *
     * @param string  $file     file path
     * @param bool    $byAlias  use aliases to parse or not
     * @param bool    $validate process validations after parsing or not
     * @param ?string $group    group of attributes
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function readCache(
        string $file,
        bool $byAlias = true,
        bool $validate = true,
        ?string $group = null,
    ): static {
        return static::fromArray(
            raw: include $file,
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            group: $group,
        );
    }

    /**
     * Same as toArray()
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray(dump:true, byAlias:true);
    }

    /**
     * Pretty object representation
     *
     * @return string
     */
    public function __toString(): string
    {
        return static::class . $this->toJSON(
            pretty: true,
            skipNulls: false,
            byAlias: false,
        );
    }

    /**
     * Creates copy with applied updates.
     * Use empty updates to create identical copy
     *
     * @param array<string,mixed> $updates  array {name: new_value}
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations or not
     * @param ?string             $group    group of attributes
     *
     * @return static
     */
    public function update(
        array $updates = [],
        bool $byAlias = false,
        bool $validate = true,
        ?string $group = null,
    ): static {
        $fields = array_merge($this->getFields(byAlias: $byAlias, group: $group), $updates);
        $copy = new static(...array_values($fields)); // @phpstan-ignore-line

        if ($validate) {
            $copy->validate(throw: true, stopOnFail: false, group: $group);
        }

        return $copy;
    }

    /**
     * Process validations (recursively in all subschemas)
     *
     * @param bool    $throw      thow ValidationException instead of returning `false`
     * @param bool    $stopOnFail stop on first failed check
     * @param bool    $getFails   return bool result or array or fails
     * @param ?string $group      group of attributes
     *
     * @return ($getFails is true ? array<string,array> : bool)
     *
     * @throws ValidationException
     */
    public function validate(
        bool $throw = false,
        bool $stopOnFail = false,
        bool $getFails = false,
        ?string $group = null,
    ): array|bool {
        $failed = [];
        $propertiesAttributes = $this->_getPropertiesAttributes(false, group: $group);

        foreach ($this->getFields() as $name => $field) {
            $fieldValidations = $propertiesAttributes[$name]->getMany(ValidateAttribute::class, false);
            $break = false;

            foreach ($fieldValidations as $validation) {
                if (!$validation->check($field, $this)) {
                    $failed[$name][]= $validation->getErrorMessage($field);
                    if ($stopOnFail) {
                        $break = true;
                        break;
                    }
                }
            }
            if ($break) {
                break;
            }

            if ($field instanceof SchemaInterface) {
                $fieldFails = $field->validate(throw: $throw, stopOnFail: $stopOnFail, getFails: true, group: $group);
                if ($fieldFails) {
                    foreach ($fieldFails as $fail) {
                        $failed[$name][] = $fail;
                    }
                    if ($stopOnFail) {
                        $break = true;
                        break;
                    }
                }
            }
            if ($break) {
                break;
            }

            if (is_array($field) && !empty($field) && end($field) instanceof SchemaInterface) {
                foreach ($field as $key => $val) {
                    $valFails = $val->validate(throw: $throw, stopOnFail: $stopOnFail, getFails: true, group: $group);
                    if ($valFails) {
                        foreach ($valFails as $fail) {
                            $failed[$name][$key][] = $fail;
                        }
                        if ($stopOnFail) {
                            $break = true;
                            break;
                        }
                    }
                }
            }
            if ($break) {
                break;
            }
        }

        if ($failed && $throw) {
            throw new ValidationException(
                "Validation for field(s) `" . implode('`, `', array_keys($failed)) . "` failed:\n" .
                json_encode($failed, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
            );
        }

        if ($getFails) {
            return $failed;
        } else {
            return empty($failed);
        }
    }

    /**
     * Parse an array of arrays as array of schemas.
     * All sub-arrays must use identical datetime format and same aliases.
     * Produced array will preserve original keys
     *
     * @param array[]                          $rows     array of arrays
     * @param bool                             $byAlias  use aliases to parse or not
     * @param bool                             $parse    parse strings into DateTimeInterface/Enum or not
     * @param 'no'|'throw'|'exclude'|'include' $validate what to do with rows that doesn't meet validation rules
     * @param bool                             $reduce   erase source array while parsing
     * @param ?string                          $group    group of attributes
     *
     * @return static[]
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function fromArrayMultiple(
        array &$rows,
        bool $byAlias = true,
        bool $parse = true,
        string $validate = 'no',
        bool $reduce = false,
        ?string $group = null,
    ): array {
        $result = [];

        if ($validate == 'no') {
            foreach ($rows as $rowindex => $row) {
                $result[$rowindex] = self::fromArray(
                    raw: $row,
                    byAlias: $byAlias,
                    validate: false,
                    parse: $parse,
                    group: $group,
                );
                if ($reduce) {
                    unset($rows[$rowindex]);
                }
            }
        } elseif ($validate == 'throw') {
            foreach ($rows as $rowindex => $row) {
                $result[$rowindex] = self::fromArray(
                    raw: $row,
                    byAlias: $byAlias,
                    validate: true,
                    parse: $parse,
                    group: $group,
                );
                if ($reduce) {
                    unset($rows[$rowindex]);
                }
            }
        } elseif ($validate == 'exclude') {
            foreach ($rows as $rowindex => $row) {
                $schema = self::fromArray(
                    raw: $row,
                    byAlias: $byAlias,
                    validate: false,
                    parse: $parse,
                    group: $group,
                );
                if ($schema->validate(throw: false, stopOnFail: true, group: $group)) {
                    $result[$rowindex] = $schema;
                }
                if ($reduce) {
                    unset($rows[$rowindex]);
                }
            }
        } elseif ($validate == 'include') {
            foreach ($rows as $rowindex => $row) {
                $schema = self::fromArray(
                    raw: $row,
                    byAlias: $byAlias,
                    validate: false,
                    parse: $parse,
                    group: $group,
                );
                if (!$schema->validate(throw: false, stopOnFail: true, group: $group)) {
                    $result[$rowindex] = $schema;
                }
                if ($reduce) {
                    unset($rows[$rowindex]);
                }
            }
        }

        return $result;
    }

    /**
     * Parse JSON as array of schemas.
     * All sub-arrays must use identical datetime format and same aliases.
     * Produced array will preserve original keys
     *
     * @param string                           $rows     rows
     * @param bool                             $byAlias  use aliases to parse or not
     * @param 'no'|'throw'|'exclude'|'include' $validate what to do with rows that doesn't meet validation rules
     * @param ?string                          $group    group of attributes
     *
     * @return static[]
     *
     * @throws \JsonException
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function fromJSONMultiple(
        string $rows,
        bool $byAlias = true,
        string $validate = 'no',
        ?string $group = null,
    ): array {
        $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR);
        return static::fromArrayMultiple(
            rows: $rows,
            byAlias: $byAlias,
            parse: true,
            validate: $validate,
            reduce: true,
            group: $group,
        );
    }

    /**
     * Check type is a schemantic object
     *
     * @param \ReflectionType $type param type as string
     *
     * @return bool
     */
    private static function _isSchema(\ReflectionType $type): bool
    {
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $subtype) {
                if (self::_isSchema($subtype)) {
                    return true;
                }
            }
            return false;
        }

        if ($type->isBuiltin()) {
            return false;
        }

        $type = str_replace(['[]', '?', '|null', 'null|'], '', (string)$type);

        if (!class_exists($type)) {
            return false;
        }

        return (
            in_array(SchemaInterface::class, class_implements($type)) ||
            in_array(Schema::class, class_parents($type))
        );
    }

    /**
     * Parse item from source array
     *
     * @param mixed|object    $value              value
     * @param string          $name               array-key
     * @param \ReflectionType $type               type
     * @param Group           $propertyAttributes property attributes
     * @param Group           $schemaAttributes   schema attributes
     *
     * @return mixed|object
     *
     * @throws ParsingException
     */
    private static function _parse(
        $value,
        string $name,
        \ReflectionType $type,
        Group $propertyAttributes,
        Group $schemaAttributes,
    ): mixed {
        $parser = $propertyAttributes->getOne(ParseInterface::class);
        if ($parser) {
            return $parser->parse($value, static::class);
        }

        $strType = (string)$type;

        if ($value instanceof $strType) {
            return $value;
        }

        if ($type->allowsNull() && is_null($value)) {
            return null;
        }

        $strType = str_replace('?', '', $strType);

        if ($strType === 'array' || mb_strpos($strType, '[]') !== false) {
            if (is_array($value)) {
                $strType = str_replace('[]', '', $strType);
                $result = [];
                foreach ($value as $k => $v) {
                    $result[$k] = self::_parseOne(
                        value: $v,
                        name: "$name.$k",
                        type: $type,
                        propertyAttributes: $propertyAttributes,
                        schemaAttributes: $schemaAttributes,
                    );
                }
                return $result;
            } else {
                throw new ParsingException("Value $name expected to be array, got " . gettype($value));
            }
        }

        return self::_parseOne(
            value: $value,
            name: $name,
            type: $type,
            propertyAttributes: $propertyAttributes,
            schemaAttributes: $schemaAttributes,
        );
    }

    /**
     * Parse item element
     *
     * @param mixed|object    $value              value
     * @param string          $name               array-key
     * @param \ReflectionType $type               type as string
     * @param Group           $propertyAttributes property attributes
     * @param Group           $schemaAttributes   schema attributes
     *
     * @return mixed|object
     *
     * @throws ParsingException
     * @throws DateParsingException
     */
    private static function _parseOne(
        $value,
        string $name,
        \ReflectionType $type,
        Group $propertyAttributes,
        Group $schemaAttributes,
    ): mixed {
        $strType = (string)$type;
        $strType = str_replace('?', '', $strType);

        if ($value instanceof $strType) {
            return $value;
        }

        if ($strType == \DateTimeInterface::class || is_subclass_of($strType, \DateTimeInterface::class)) {
            /**
             * @var class-string<\DateTimeInterface>
             */
            if (is_string($value)) {
                $dtFormat = $propertyAttributes->getOne(DateTimeFormat::class)?->format
                    ?? $schemaAttributes->getOne(DateTimeFormat::class)?->format
                    ?? 'Y-m-d\TH:i:s';
                if ($strType == \DateTimeInterface::class) {
                    $result = \DateTimeImmutable::createFromFormat($dtFormat, $value);
                } else {
                    $result = $strType::createFromFormat($dtFormat, $value);
                }
                if ($result) {
                    return $result;
                } else {
                    throw new DateParsingException("Cannot parse $name as $strType: bad datetime format");
                }
            } elseif (is_int($value)) {
                return (new $strType)->setTimestamp($value);
            } else {
                throw new ParsingException("cannot parse $name as date/time type $type");
            }
        }

        if (is_int($value) || is_string($value)) {
            if (is_subclass_of($strType, \BackedEnum::class)) {
                if (!$value instanceof \BackedEnum) {
                    try {
                        return $strType::from($value);
                    } catch (\ValueError $ve) {
                        if (defined("$strType::$value")) {
                            return constant("$strType::$value");
                        }
                        throw $ve;
                    }
                }
            }

            if (is_subclass_of($strType, \UnitEnum::class)) {
                if (!$value instanceof \UnitEnum) {
                    return $strType::cases()[$value];
                }
            }
        }

        throw new ParsingException("cannot parse $name as $type");
    }

    /**
     * Convert (if possible) value to string or int representation
     *
     * @param mixed $value              value
     * @param Group $propertyAttributes property attributes
     * @param Group $schemaAttributes   schema attributes
     *
     * @return mixed|string
     */
    private static function _dump(
        $value,
        Group $propertyAttributes,
        Group $schemaAttributes,
    ): mixed {
        $dumper = $propertyAttributes->getOne(DumpInterface::class, strict: false);
        if ($dumper) {
            return $dumper->dump($value, static::class);
        }

        if ($value instanceof \DateTimeInterface) {
            $format = $propertyAttributes->getOne(DateTimeFormat::class)?->format
                ?? $schemaAttributes->getOne(DateTimeFormat::class)?->format
                ?? 'Y-m-d\TH:i:s';
            if ($format == 'unix') {
                return $value->getTimestamp();
            }
            return $value->format($format);
        }
        if ($value instanceof \BackedEnum) { // @phpstan-ignore-line
            return $value->value;
        }
        if ($value instanceof \UnitEnum) { // @phpstan-ignore-line
            return $value->name;
        }

        return $value;
    }
}
