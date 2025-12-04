<?php

namespace Schemantic\Attribute\Parse;

use Schemantic\Attribute\SingleAttributeInterface;
use Schemantic\SchemaInterface;

/**
 * Interface for parse attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface ParseInterface extends SingleAttributeInterface, BaseParseInterface
{
    /**
     * Parse value
     *
     * @param mixed                         $value  raw value to parse from
     * @param class-string<SchemaInterface> $schema schema asking to parse
     *
     * @return mixed
     */
    public function parse($value, string $schema);
}
