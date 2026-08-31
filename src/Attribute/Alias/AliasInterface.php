<?php

namespace Schemantic\Attribute\Alias;

use Schemantic\Attribute\Group\SingleAttributeInterface;

/**
 * Attribute that can change field name
 *
 * @category Library
 * @package  Schemantic\Attribute\Alias
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface AliasInterface extends SingleAttributeInterface
{
    /**
     * @param string $name original field name
     *
     * @return string aliased field name
     */
    public function getAlias(string $name): string;
}
