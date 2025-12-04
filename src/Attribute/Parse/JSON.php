<?php

namespace Schemantic\Attribute\Parse;

use Attribute;

/**
 * Parse nested JSON
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class JSON implements ParseInterface
{
    /**
     * JSON contructor
     *
     * @param int $flags `json_decode` flags
     */
    public function __construct(public readonly int $flags = JSON_THROW_ON_ERROR)
    {
    }

    public function parse($value, string $schema)
    {
        return json_decode($value, true, 512, $this->flags);
    }
}
