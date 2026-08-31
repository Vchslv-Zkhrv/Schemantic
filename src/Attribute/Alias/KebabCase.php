<?php

namespace Schemantic\Attribute\Alias;

use Attribute;

/**
 * Creates alias by casting field name to `kebab-case`
 *
 * @category Library
 * @package  Schemantic\Attribute\Alias
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class KebabCase extends CaseAttribute
{
    /**
     * KebabCase constructor.
     *
     * @param bool $upperCase convert to `UPPER-KEBAB-CASE`
     */
    public function __construct(public readonly bool $upperCase = false)
    {
    }

    public function getAlias(string $name): string
    {
        $words = $this->split($name);
        return implode('-', array_map($this->upperCase ? 'strtoupper' : 'strtolower', $words));
    }
}
