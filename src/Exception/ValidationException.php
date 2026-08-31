<?php

namespace Schemantic\Exception;

/**
 * @category Library
 * @package  Schemantic\Exception
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
class ValidationException extends SchemaException
{
    /**
     * @param array<string,string[]> $fields failed rules per field
     */
    public function __construct(public readonly array $fields)
    {
        parent::__construct(
            message: "Validation for field(s) `" . implode('`, `', array_keys($fields)) . "` failed",
            code: 400,
        );
    }
}
