<?php

namespace Schemantic;

/**
 * Recursively parsing structure.
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
abstract class Schema implements SchemaInterface
{
    use SchemaTrait;

    /**
     * Schema constructor.
     *
     * You do not have to call `parent::__construct()` if you do not want to initiate validation.
     */
    public function __construct()
    {
        $this->validate(throw: true, stopOnFail: false);
    }
}
