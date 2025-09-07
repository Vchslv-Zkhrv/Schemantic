<?php

namespace Schemantic;

use Schemantic\Exception\ValidationException;
use Schemantic\Exception\ParsingException;

/**
 * Recursively parsing structure interface.
 *
 * Supports built-in types, enums, datetime, nested structures
 * and arrays of them all.
 *
 * Can read arrays, jsons, objects, query params, env variables and more.
 *
 * @category Library
 * @package  Schemantic
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface SchemaInterface extends \JsonSerializable, \Stringable
{
    /**
     * Check schema contains subschemas or not
     *
     * @return bool
     */
    public static function isPlain(): bool;

    /**
     * Check that all schema's fields types are builtins, \DateTimeInterface or \UnitEnum
     *
     * @return bool
     */
    public static function isBuiltin(): bool;

    /**
     * Parses JSON into Schema
     *
     * @param string              $json     JSON string
     * @param array<string,mixed> $extra    additional fields. Can override JSON fields.
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
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
    ): static;

    /**
     * Reads values from environment variables
     *
     * @param bool                $byAlias  use aliases to parse or not
     * @param array<string,mixed> $extra    unaliased. Can override env params.
     * @param bool                $validate process validations after parsing or not
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
    ): static;

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
    ): static;

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
    ): object;

    /**
     * Updates object with own fields.
     * Uses both properties (including virtual) and setter methods
     *
     * @param object              $object  object to update
     * @param array<string,mixed> $extra   Additional fields (not aliased). Can override env params
     * @param bool                $byAlias use aliases to parse or not
     *
     * @return void
     *
     * @throws \Exception some exceptions that can be raised by object `set...` or `is...` methods
     */
    public function updateObject(
        object &$object,
        array $extra = [],
        bool $byAlias = false
    ): void;

    /**
     * Parses array as Schema
     *
     * @param array<stirng|int,mixed> $raw      both associative and non-associative arrays allowed
     * @param bool                    $byAlias  use aliases to parse or not
     * @param bool                    $validate process validations after parsing or not
     * @param bool                    $parse    parse strings into DateTimeInterface/Enum or not
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
    ): static;

    /**
     * Dumps schema into JSON
     *
     * @param bool $pretty    pretty print + unescaped slashes + unescaped unicode
     * @param bool $skipNulls remove `null` fields from JSON
     * @param bool $byAlias   use aliases to parse or not
     *
     * @return string
     */
    public function toJSON(
        bool $pretty = false,
        bool $skipNulls = false,
        bool $byAlias = false,
    ): string;

    /**
     * Build query string or form-data body
     *
     * @param bool     $skipNulls remove `null` fields from query string
     * @param bool     $byAlias   apply field aliases
     * @param string[] $omit      fields names to omit
     *
     * @return string
     */
    public function toQuery(
        bool $skipNulls = true,
        bool $byAlias = true,
        array $omit = [],
    ): string;

    /**
     * Parse query string or form-data body
     *
     * @param string              $query    query string or form-data content
     * @param bool                $byAlias  use aliases to parse or not
     * @param bool                $validate process validations after parsing or not
     * @param array<string,mixed> $extra    additional fields. Can override query params
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
    ): static;

    /**
     * Get `__construct` params names
     *
     * @param bool $byAlias apply field aliases
     *
     * @return array<int,string>
     */
    public static function getContructParams(bool $byAlias = false): array;

    /**
     * Returns fields as associative array as-is
     *
     * @param bool $byAlias apply field aliases
     *
     * @return array<string,mixed>
     */
    public function getFields(bool $byAlias = false): array;

    /**
     * Returns fields as array. Dumps subschemas into subarrays
     *
     * @param bool $skipNulls remove `null` fields from array
     * @param bool $byAlias   apply field aliases
     * @param bool $dump      convert dates and enums to strings
     *
     * @return array<string,mixed>
     */
    public function toArray(
        bool $skipNulls = false,
        bool $byAlias = false,
        bool $dump = false,
    ): array;

    /**
     * Writes php-valid cache
     *
     * @param string $file file path
     *
     * @return void
     */
    public function writeCache(string $file): void;

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
    ): static;

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
    ): static;

    /**
     * Process validations (recursively in all subschemas)
     *
     * @param bool $throw      thow ValidationException instead of returning `false`
     * @param bool $stopOnFail stop on first failed check
     * @param bool $getFails   return bool result or array or fails
     *
     * @return ($getFails is true ? array<string,array> : bool)
     *
     * @throws ValidationException
     */
    public function validate(
        bool $throw = false,
        bool $stopOnFail = false,
        bool $getFails = false,
    ): array|bool;

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
    ): array;

    /**
     * Parse JSON as array of schemas.
     * All sub-arrays must use identical datetime format and same aliases.
     * Produced array will preserve original keys
     *
     * @param string                           $rows     rows
     * @param bool                             $byAlias  use aliases to parse or not
     * @param 'no'|'throw'|'exclude'|'include' $validate what to do with rows that doesn't meet validation rules
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
    ): array;
}
