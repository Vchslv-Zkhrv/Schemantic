<?php

namespace Schemantic\Attribute\Dump;

use Attribute;

/**
 * Dump as nested JSON
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class JSON implements DumpInterface
{
    /**
     * JSON contructor
     *
     * @param int $flags `json_encode` flags
     */
    public function __construct(public readonly int $flags = JSON_THROW_ON_ERROR)
    {
    }

    public function dump($value, string $schema): string
    {
        return json_encode($value, $this->flags);
    }
}
