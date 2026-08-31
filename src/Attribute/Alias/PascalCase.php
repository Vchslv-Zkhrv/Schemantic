<?php

namespace Schemantic\Attribute\Alias;

use Attribute;

/**
 * Creates alias by casting field name to `PascalCase`
 *
 * @category Library
 * @package  Schemantic\Attribute\Alias
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class PascalCase extends CaseAttribute
{
    public function getAlias(string $name): string
    {
        $words = $this->split($name);
        return implode('', array_map('ucfirst', array_map('strtolower', $words)));
    }
}
