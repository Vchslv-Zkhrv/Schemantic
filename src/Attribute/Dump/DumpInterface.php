<?php

namespace Schemantic\Attribute\Dump;

use Schemantic\Attribute\SingleAttributeInterface;
use Schemantic\SchemaInterface;

/**
 * Interface for dump attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Dump
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface DumpInterface extends SingleAttributeInterface, BaseDumpInterface
{
    /**
     * Dump value
     *
     * @param mixed                         $value  raw value to dump to
     * @param class-string<SchemaInterface> $schema schema asking to dump
     *
     * @return mixed
     */
    public function dump($value, string $schema);
}
