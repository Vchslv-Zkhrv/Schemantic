<?php

namespace Schemantic;

use Schemantic\Exception\SchemaException;
use Schemantic\Exception\ParsingException;
use Schemantic\Exception\ValidationException;
use Schemantic\Type\Date\Date;
use Schemantic\Type\Date\DateImmutable;
use Schemantic\Type\Time\Time;
use Schemantic\Type\Time\TimeImmutable;

/**
 * Recursively parsing structure trait.
 *
 * Supports built-in types, enums, datetime, nested structures and arrays of them all.
 * Can read arrays, jsons, objects, query params, env variables and more.
 *
 * Combine with SchemaInterface to use Schemantic with your DTOs
 *
 * @category Library
 * @package  Schemantic
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
trait SchemaTrait
{
    /**
     * Override to set fields aliases
     *
     * Example: `[ 'unaliased_field' => 'aliasedField' ]`
     *
     * @return array<string,string> `{unaliased: alias}`
     */
    public static function getAliases(): array
    {
        return [];
    }

    /**
     * Override to set field validations
     *
     * Example: ` [ 'password' => (mb_strlen($this->password) > 7) ]`
     *
     * @return array<string,bool> `{field_name: validation}`
     */
    public function getValidations(): array
    {
        return [];
    }

    /**
     * Override to set default parse/dump format for Date\DateImmutable types
     *
     * @return string `Y-m-d` by default
     */
    public static function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    /**
     * Override to set default parse/dump format for Time\TimeImmutable types
     *
     * @return string `H:i:s` by default
     */
    public static function getTimeFormat(): string
    {
        return 'H:i:s';
    }

    /**
     * Override to set default parse/dump format for DateTime\DateTimeImmutable types
     *
     * @return string `Y-m-d\TH:i:s` by default
     */
    public static function getDateTimeFormat(): string
    {
        return 'Y-m-d\TH:i:s';
    }

    /**
     * Check schema contains subschemas or not
     *
     * @return bool
     */
    public static function isPlain(): bool
    {
        foreach (self::_getMethodParams(static::class, '__construct') as $name => $type) {
            if ($type && self::_isSchema($type)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check that all schema's fields types are builtins, \DateTimeInterface or \UnitEnum
     *
     * @return bool
     */
    public static function isBuiltin(): bool
    {
        $reflection = new \ReflectionMethod(static::class, '__construct');
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $strType = $type->__toString();
            if (!$type->isBuiltin()
                && !$strType instanceof \DateTimeInterface
                && !$strType instanceof \UnitEnum
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively parse subarrays as subschemas
     *
     * @param array<string,mixed> $raw            source array
     * @param bool                $byAlias        apply aliases
     * @param bool                $validate       process validation after parsing
     * @param bool                $parse          parse strings as DateTimes and enums
     * @param string              $dateTimeFormat DateTime\DateTimeImmutable parse format
     * @param string              $dateFormat     Date\DateImmutable parse format
     * @param string              $timeFormat     Time\TimeImmutable parse format
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
        string $dateTimeFormat,
        string $dateFormat,
        string $timeFormat
    ): array {
        $params = self::_getMethodParams(static::class, '__construct');

        foreach ($params as $name => $type) {

            if ($type === null) {
                continue;
            }

            if (!self::_isSchema($type)) {
                if ($parse && isset($raw[$name])) {
                    $raw[$name] = self::_parse(
                        value: $raw[$name],
                        name: $name,
                        as: $type,
                        dateTimeFormat: $dateTimeFormat,
                        dateFormat: $dateFormat,
                        timeFormat: $timeFormat,
                    );
                }
                continue;
            }

            if (mb_substr($type, 0, 1) === '?') {

                if (!array_key_exists($name, $raw)) {
                    continue;
                } elseif ($raw[$name] == null) {
                    continue;
                } else {
                    $type = str_replace('?', '', $type);
                }
            }

            $schemaValues = $raw[$name];
            if ($schemaValues instanceof SchemaInterface) {
                $schemaValues = $schemaValues->toArray(
                    skipNulls: false,
                    byAlias: false,
                    dump: false
                );
            }

            if (!is_array($schemaValues)) {
                $cls = gettype($schemaValues);
                throw new SchemaException("$type: Parameter $name must be an array or schema, $cls given");
            }

            try {

                if (mb_strpos($type, '[]') !== false) {
                    $type = str_replace('[]', '', $type);

                    foreach ($schemaValues as $key => $values) {
                        $raw[$name][$key] = $type::fromArray(
                            raw: $values,
                            byAlias: $byAlias,
                            validate: $validate,
                            parse: $parse,
                            dateTimeFormat: $dateTimeFormat,
                            dateFormat: $dateFormat,
                            timeFormat: $timeFormat
                        );
                    }
                } else {
                    $raw[$name] = $type::fromArray(
                        raw: $schemaValues,
                        byAlias: $byAlias,
                        validate: $validate,
                        parse: $parse,
                        dateTimeFormat: $dateTimeFormat,
                        dateFormat: $dateFormat,
                        timeFormat: $timeFormat
                    );
                }

            } catch (\Throwable $e) {

                throw new SchemaException("$type: {$e->getMessage()}");

            }

        }

        foreach ($raw as $key => $value) {
            if (!array_key_exists($key, $params)) {
                unset($raw[$key]);
            }
        }

        return $raw;
    }

    /**
     * Parses JSON into Schema
     *
     * @param string              $json           JSON string
     * @param array<string,mixed> $extra          additional fields. Can override JSON fields.
     * @param bool                $byAlias        use aliases to parse or not
     * @param bool                $validate       process validations after parsing or not
     * @param ?string             $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string             $dateFormat     `getDateFormat()` by default
     * @param ?string             $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): static {
        $raw = json_decode($json, true, flags:JSON_THROW_ON_ERROR);
        return static::fromArray(
            raw: array_merge($raw, $extra),
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
        );
    }

    /**
     * Reads values from environment variables
     *
     * @param bool                $byAlias        use aliases to parse or not
     * @param array<string,mixed> $extra          unaliased. Can override env params.
     * @param bool                $validate       process validations after parsing or not
     * @param ?string             $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string             $dateFormat     `getDateFormat()` by default
     * @param ?string             $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): static {
        $keys = self::getContructParams(false);
        $names = self::_applyAlias($keys);

        $values = array_combine(
            $keys,
            array_map(
                function (string $n) {
                    $env = getenv($n);
                    return $env==false ? null : $env;
                },
                $names
            )
        );

        return static::fromArray(
            raw: array_merge($values, $extra),
            validate: $validate,
            byAlias: $byAlias,
            parse: true,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
        );
    }

    /**
     * Reads both object public properties (including virtual) and gettters to build itslef
     *
     * @param object              $object   object to parse
     * @param array<string,mixed> $extra    Additional fields (not aliased). Can override object fields
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
     *
     * @return static
     *
     * @throws ValidationException
     */
    public static function fromObject(
        object $object,
        array $extra = [],
        bool $byAlias = false,
        bool $validate = true
    ): static {
        $names = self::getContructParams($byAlias);
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
            parse: true
        );
    }

    /**
     * Create object from class & fill it.
     * Uses construct params, public fields (including virtual) and setter methods
     *
     * @param class-string<T>     $class   class to build from
     * @param array<string,mixed> $extra   Additional fields (not aliased). Can override env params.
     * @param bool                $byAlias use aliases to parse or not
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
        bool $byAlias = false
    ): object {
        $fields = array_merge($this->getFields($byAlias), $extra);

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
     * @param T                   $object  object to update
     * @param array<string,mixed> $extra   Additional fields (not aliased). Can override env params
     * @param bool                $byAlias use aliases to parse or not
     *
     * @template T
     *
     * @return void
     *
     * @throws \Exception some exceptions that can be raised by object `set...` or `is...` methods
     */
    public function updateObject(
        object &$object,
        array $extra = [],
        bool $byAlias = false
    ): void {
        $fields = array_merge($this->getFields($byAlias), $extra);

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
     * @param array<stirng|int,mixed> $raw            both associative and non-associative arrays allowed
     * @param bool                    $byAlias        use aliases to parse or not
     * @param bool                    $validate       process validations after parsing or not
     * @param bool                    $parse          parse strings into DateTimeInterface/Enum or not
     * @param ?string                 $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string                 $dateFormat     `getDateFormat()` by default
     * @param ?string                 $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): static {
        if (array_is_list($raw)) {

            $params = static::getContructParams();

            if (count($raw) < count($params)) {
                $params = array_slice($params, 0, count($raw));
            } elseif (count($raw) > count($params)) {
                $raw = array_slice($raw, 0, count($params));
            }

            $raw = array_combine($params, $raw);
            $byAlias = false;

        }

        if ($byAlias) {
            $aliases = array_flip(static::getAliases());
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
                dateTimeFormat: $dateTimeFormat ?? static::getDateTimeFormat(),
                dateFormat: $dateFormat ?? static::getDateFormat(),
                timeFormat: $timeFormat ?? static::getTimeFormat()
            );
            $schema = new static(...$values);

        } catch (SchemaException $se) {

            throw $se;

        } catch (\ArgumentCountError $ce) {

            throw new SchemaException('Field count mismatch: ' . $ce->getMessage());

        }

        if ($validate) {
            $schema->validate(true, false);
        }

        return $schema;
    }

    /**
     * Dumps schema into JSON
     *
     * @param bool    $pretty         pretty print + unescaped slashes + unescaped unicode
     * @param bool    $skipNulls      remove `null` fields from JSON
     * @param bool    $byAlias        use aliases to parse or not
     * @param ?string $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string $dateFormat     `getDateFormat()` by default
     * @param ?string $timeFormat     `getTimeFormat()` by default
     *
     * @return string
     */
    public function toJSON(
        bool $pretty=false,
        bool $skipNulls=false,
        bool $byAlias=false,
        ?string $dateTimeFormat = 'Y-m-d\TH:i:s',
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): string {
        $dump = $this->toArray(
            skipNulls: $skipNulls,
            byAlias: $byAlias,
            dump: true,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
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
     * @param bool     $skipNulls      remove `null` fields from query string
     * @param bool     $byAlias        apply field aliases
     * @param string[] $omit           fields names to omit
     * @param ?string  $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string  $dateFormat     `getDateFormat()` by default
     * @param ?string  $timeFormat     `getTimeFormat()` by default
     *
     * @return string
     */
    public function toQuery(
        bool $skipNulls = true,
        bool $byAlias = true,
        array $omit = [],
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): string {
        return http_build_query(
            array_diff_key(
                $this->toArray(
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: true,
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                ),
                array_flip($omit)
            )
        );
    }

    /**
     * Parse query string or form-data body
     *
     * @param string              $query          query string or form-data content
     * @param bool                $byAlias        use aliases to parse or not
     * @param bool                $validate       process validations after parsing or not
     * @param array<string,mixed> $extra          additional fields. Can override query params
     * @param ?string             $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string             $dateFormat     `getDateFormat()` by default
     * @param ?string             $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): static {
        $array = [];
        mb_parse_str($query, $array);
        return static::fromArray(
            raw: array_merge($array, $extra),
            byAlias: $byAlias,
            validate: $validate,
            parse: true,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
        );
    }

    /**
     * Recursively parse subschemas into subarrays
     *
     * @param array  $fields         fields as-is
     * @param bool   $skipNulls      skip null values
     * @param bool   $byAlias        apply aliases
     * @param bool   $dump           convert \DateTimeInterface and Enums as strings
     * @param string $dateTimeFormat DateTime\DateTimeImmutable parse format
     * @param string $dateFormat     Date\DateImmutable parse format
     * @param string $timeFormat     Time\TimeImmutable parse format
     *
     * @return array<string, mixed>
     */
    private static function _dumpRecursive(
        array $fields,
        bool $skipNulls,
        bool $byAlias,
        bool $dump,
        string $dateTimeFormat,
        string $dateFormat,
        string $timeFormat
    ): array {
        $array = [];

        foreach ($fields as $key => $value) {

            if ($value instanceof SchemaInterface || $value instanceof self) {
                $array[$key] = $value->toArray(
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: $dump,
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                );
            } elseif (is_array($value)) {
                $array[$key] = self::_dumpRecursive(
                    fields: $value,
                    skipNulls: $skipNulls,
                    byAlias: $byAlias,
                    dump: $dump,
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                );
            } elseif ($dump) {
                $array[$key] = self::_dump(
                    value: $value,
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                );
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Replace keys with aliases in array
     *
     * @param array<int,string> $names unaliased array
     *
     * @return array<int,string>
     */
    private static function _applyAlias(array $names): array
    {
        $aliases = static::getAliases();
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
     * @param bool $byAlias apply field aliases
     *
     * @return array<int,string>
     */
    public static function getContructParams(bool $byAlias=false): array
    {
        $names = array_map(
            fn (\ReflectionParameter $p) => $p->name,
            (new \ReflectionMethod(static::class, '__construct'))->getParameters()
        );

        if ($byAlias) {
            return self::_applyAlias($names);
        } else {
            return $names;
        }
    }

    /**
     * Returns fields as associative array as-is
     *
     * @param bool $byAlias apply field aliases
     *
     * @return array<string,mixed>
     */
    public function getFields(bool $byAlias=false): array
    {
        $keys = $params = self::getContructParams(false);

        if ($byAlias) {
            $keys = self::_applyAlias($keys);
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
     * @param bool    $skipNulls      remove `null` fields from array
     * @param bool    $byAlias        apply field aliases
     * @param bool    $dump           convert dates and enums to strings
     * @param ?string $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string $dateFormat     `getDateFormat()` by default
     * @param ?string $timeFormat     `getTimeFormat()` by default
     *
     * @return array<string,mixed>
     */
    public function toArray(
        bool $skipNulls = false,
        bool $byAlias = false,
        bool $dump = false,
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): array {
        $result = self::_dumpRecursive(
            fields: $this->getFields($byAlias),
            skipNulls: $skipNulls,
            byAlias: $byAlias,
            dump: $dump,
            dateTimeFormat: $dateTimeFormat ?? static::getDateTimeFormat(),
            dateFormat: $dateFormat ?? static::getDateFormat(),
            timeFormat: $timeFormat ?? static::getTimeFormat()
        );

        if ($byAlias) {
            foreach (static::getAliases() as $old => $new) {
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
     * @param string $file file path
     *
     * @return void
     */
    public function writeCache(string $file): void
    {
        $export = var_export(
            $this->toArray(
                skipNulls: false,
                byAlias: false,
                dump: false
            ),
            true
        );

        file_put_contents($file, "<?php\n\nreturn $export;");
    }

    /**
     * Reads php cache (builded with writeCache or var_export)
     *
     * @param string $file     file path
     * @param bool   $byAlias  use aliases to parse or not
     * @param bool   $validate process validations after parsing or not
     *
     * @return static
     *
     * @throws ValidationException
     * @throws ParsingException
     */
    public static function readCache(
        string $file,
        bool $byAlias = true,
        bool $validate = true
    ): static {
        return static::fromArray(
            raw: file_get_contents($file),
            byAlias: $byAlias,
            validate: $validate,
            parse: true
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
            byAlias: true
        );
    }

    /**
     * Creates copy with applied updates.
     * Use empty updates to create identical copy
     *
     * @param array<string,mixed> $updates  array {name: new_value}
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations or not
     *
     * @return static
     */
    public function update(
        array $updates = [],
        bool $byAlias = false,
        bool $validate = true
    ): static {
        $fields = array_merge($this->getFields($byAlias), $updates);
        $copy = new static(...array_values($fields));

        if ($validate) {
            $copy->validate(true);
        }

        return $copy;
    }

    /**
     * Creates deep copy
     *
     * @return static
     */
    public function copy(): static
    {
        return unserialize(serialize($this));
    }

    /**
     * Process validations (recursively in all subschemas)
     *
     * @param bool $throw      thow ValidationException instead of returning `false`
     * @param bool $stopOnFail stop on first failed check
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function validate(
        bool $throw = false,
        bool $stopOnFail = false
    ): bool {
        $failed = [];
        $validations = $this->getValidations();

        foreach ($this->getFields() as $name => $field) {

            if (array_key_exists($name, $validations)) {
                if (!$validations[$name]) {
                    $failed[] = $name;
                    if ($stopOnFail) {
                        break;
                    }
                    continue;
                }
            }

            if ($field instanceof SchemaInterface) {
                if (!$field->validate($throw, $stopOnFail)) {
                    $failed[] = $name;
                    if ($stopOnFail) {
                        break;
                    }
                    continue;
                }
            }

            if (is_array($field) && !empty($field) && end($field) instanceof SchemaInterface) {
                foreach ($field as $key => $val) {
                    if (!$val->validate($throw, $stopOnFail)) {
                        $failed[] = $name . "[$key]";
                        if ($stopOnFail) {
                            break;
                        }
                        continue;
                    }
                }
            }
        }

        if ($failed && $throw) {
            throw new ValidationException("Validation for field(s) `" . implode('`, `', $failed) . "` failed");
        }

        return !boolval($failed);
    }

    /**
     * Parse an array of arrays as array of schemas.
     * All sub-arrays must use identical datetime format and same aliases.
     * Produced array will preserve original keys
     *
     * @param array[]                          $rows           array of arrays
     * @param bool                             $byAlias        use aliases to parse or not
     * @param bool                             $parse          parse strings into DateTimeInterface/Enum or not
     * @param 'no'|'throw'|'exclude'|'include' $validate       what to do with rows that doesn't meet validation rules
     * @param bool                             $reduce         erase source array while parsing
     * @param ?string                          $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string                          $dateFormat     `getDateFormat()` by default
     * @param ?string                          $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): array {
        $result = [];

        if ($validate == 'no') {

            foreach ($rows as $rowindex => $row) {
                $result[$rowindex] = self::fromArray(
                    raw: $row,
                    byAlias: $byAlias,
                    validate: false,
                    parse: $parse,
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
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
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
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
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                );
                if ($schema->validate(false, true)) {
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
                    dateTimeFormat: $dateTimeFormat,
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat
                );
                if (!$schema->validate(false, true)) {
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
     * @param string                           $rows           rows
     * @param bool                             $byAlias        use aliases to parse or not
     * @param 'no'|'throw'|'exclude'|'include' $validate       what to do with rows that doesn't meet validation rules
     * @param ?string                          $dateTimeFormat `getDateTimeFormat()` by default
     * @param ?string                          $dateFormat     `getDateFormat()` by default
     * @param ?string                          $timeFormat     `getTimeFormat()` by default
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
        ?string $dateTimeFormat = null,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): array {
        $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR);
        return static::fromArrayMultiple(
            rows: $rows,
            byAlias: $byAlias,
            parse: true,
            validate: $validate,
            reduce: true,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
        );
    }

    /**
     * Returns method expected arguments as name => type array.
     * If PHPdoc comment exists, takes type hints from it.
     *
     * @param class-string $className  class
     * @param string       $methodName method
     *
     * @return array<string,string>
     */
    private static function _getMethodParams(string $className, string $methodName): array
    {
        $class = new \ReflectionClass($className);
        $method = new \ReflectionMethod($className, $methodName);
        $result = [];

        $doc = $method->getDocComment();
        $params = $method->getParameters();
        foreach ($params as $param) {
            $type = $param->getType();
            $type = $type===null ? null : ($type->isBuiltin() ? null : $type->__toString());
            $name = $param->getName();
            $result[$param->getName()] = $type;
        }

        $lines = array_map(
            fn (string $l) => trim($l),
            explode('*', $doc)
        );

        $params = array_filter(
            $lines,
            fn (string $line) => mb_strpos($line, '@param') !== false
        );

        $params = array_map(
            fn (string $p) => preg_replace('/\s+/', ' ', $p),
            $params
        );

        foreach ($params as $param) {
            $words = explode(' ', $param);
            $type = $words[1];
            if (self::_isSchema($type)) {
                if (mb_strpos($type, '\\') === false) {
                    $ns = $class->getNamespaceName();
                    $type = $ns . '\\' . $type;
                }
                $name = str_replace('$', '', $words[2]);
                $result[$name] = $type;
            }
        }

        return $result;
    }

    /**
     * Check type is schemantic object
     *
     * @param string|class-string $type param type as string
     *
     * @return bool
     */
    private static function _isSchema(string $type): bool
    {
        $type = str_replace(['[]', '?'], '', $type);

        if (!class_exists($type)) {
            return false;
        }

        return (
            in_array(Schema::class, class_parents($type)) ||
            in_array(SchemaInterface::class, class_implements($type))
        );
    }

    /**
     * Parse item from source array
     *
     * @param mixed|object $value          value
     * @param string       $name           array-key
     * @param class-string $as             type as string
     * @param string       $dateTimeFormat DateTime\DateTimeImmutable parse format
     * @param string       $dateFormat     Date\DateImmutable parse format
     * @param string       $timeFormat     Time\TimeImmutable parse format
     *
     * @return mixed|object
     *
     * @throws ParsingException
     */
    private static function _parse(
        $value,
        string $name,
        string $as,
        string $dateTimeFormat,
        string $dateFormat,
        string $timeFormat
    ): mixed {
        if ($value instanceof $as) {
            return $value;
        }

        if (mb_strpos($as, '|') !== false) {
            throw new ParsingException("Cannot parse union type");
        }

        if (mb_strpos($as, '<') !== false) {
            throw new ParsingException("Cannot parse generics");
        }

        if (mb_strpos($as, '?') !== false) {
            if (is_null($value)) {
                return null;
            }
            $as = str_replace('?', '', $as);
        }

        if (mb_strpos($as, '[]') !== false) {
            if (is_array($value)) {
                $as = str_replace('[]', '', $as);
                $result = [];
                foreach ($value as $k => $v) {
                    $result[$k] = self::_parseOne(
                        value: $v,
                        name: "$name\[$k\]",
                        as: $as,
                        dateTimeFormat: $dateTimeFormat,
                        dateFormat: $dateFormat,
                        timeFormat: $timeFormat
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
            as: $as,
            dateTimeFormat: $dateTimeFormat,
            dateFormat: $dateFormat,
            timeFormat: $timeFormat
        );
    }

    /**
     * Parse item element
     *
     * @param mixed|object $value          value
     * @param string       $name           array-key
     * @param class-string $as             type as string
     * @param string       $dateTimeFormat DateTime\DateTimeImmutable parse format
     * @param string       $dateFormat     Date\DateImmutable parse format
     * @param string       $timeFormat     Time\TimeImmutable parse format
     *
     * @return mixed|object
     *
     * @throws ParsingException
     */
    private static function _parseOne(
        $value,
        string $name,
        string $as,
        string $dateTimeFormat,
        string $dateFormat,
        string $timeFormat
    ): mixed {
        if ($value instanceof $as) {
            return $value;
        }

        if ($as == \DateTimeInterface::class || is_subclass_of($as, \DateTimeInterface::class)) {

            foreach ([
                DateImmutable::class => $dateFormat,
                Date::class => $dateFormat,
                TimeImmutable::class => $timeFormat,
                Time::class => $timeFormat,
                \DateTimeImmutable::class => $dateTimeFormat,
                \DateTime::class => $dateTimeFormat
            ] as $dtClass => $dtFormat) {

                if ($as == $dtClass || is_subclass_of($as, $dtClass)) {

                    if (is_subclass_of($value, $dtClass)) {
                        return $value;
                    } elseif (is_string($value)) {
                        $result = $dtClass::createFromFormat($dtFormat, $value);
                        if ($result) {
                            return $result;
                        } else {
                            throw new ParsingException("Cannot parse $name as $as: bad datetime format");
                        }
                    } elseif (is_int($value)) {
                        return (new $dtClass)->setTimestamp($value);
                    }

                }

            }

        }

        if (is_subclass_of($as, \BackedEnum::class)) {
            if (!$value instanceof \BackedEnum) {
                return $as::tryFrom($value);
            }
        }

        if (is_subclass_of($as, \UnitEnum::class)) {
            if (!$value instanceof \UnitEnum) {
                return $as::cases()[$value];
            }
        }

        throw new ParsingException("cannot parse $name as $as");
    }

    /**
     * Convert (if possible) value to string or int representation
     *
     * @param mixed  $value          value
     * @param string $dateTimeFormat DateTime\DateTimeImmutable format
     * @param string $dateFormat     Date\DateImmutable format
     * @param string $timeFormat     Time\TimeImmutable format
     *
     * @return mixed|string
     */
    private static function _dump(
        $value,
        string $dateTimeFormat,
        string $dateFormat,
        string $timeFormat
    ): mixed {
        if ($value instanceof Date || $value instanceof DateImmutable) {
            return $value->format($dateFormat);
        }
        if ($value instanceof Time || $value instanceof TimeImmutable) {
            return $value->format($timeFormat);
        }
        if ($value instanceof \DateTimeInterface) {
            if ($dateTimeFormat == 'unix') {
                return $value->getTimestamp();
            }
            return $value->format($dateTimeFormat);
        }
        if ($value instanceof \BackedEnumCase || $value instanceof \BackedEnum) {
            return $value->value;
        }
        if ($value instanceof \UnitEnumCase || $value instanceof \UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
