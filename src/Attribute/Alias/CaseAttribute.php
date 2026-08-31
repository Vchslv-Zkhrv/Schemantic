<?php

namespace Schemantic\Attribute\Alias;

/**
 * Base class for case-switching attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Alias
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
abstract class CaseAttribute implements AliasGeneratorInterface
{
    /**
     * Splits any case into separate words
     *
     * @param string $name field name of any case
     *
     * @return string[]
     */
    protected function split(string $name): array
    {
        return preg_split(
            '/[_-]|(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])|(?<=[a-zA-Z])(?=\d)|(?<=\d)(?=[a-zA-Z])/',
            $name
        );
    }
}
