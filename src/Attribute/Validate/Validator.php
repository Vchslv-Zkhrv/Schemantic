<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\Exception\SchemaException;
use Schemantic\SchemaInterface;

/**
 * Use to set a property validation callback
 *
 * @extends ValidateAttribute<mixed>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class Validator extends ValidateAttribute
{
    /**
     * @param object|string $validator    schema method, validator instance or class
     * @param ?string       $method       validator method name
     * @param ?string       $errorMessage error message
     */
    public function __construct(
        public readonly object|string $validator,
        public readonly ?string $method = null,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        $validator = $this->validator;
        $method = $this->method;

        if (is_object($validator)) {
            if ($method === null) {
                return $validator($value);
            } elseif (method_exists($validator, $method)) {
                return $validator->$method($value);
            } else {
                throw new SchemaException($schema::class . " - Validator has no such method: $method");
            }
        }

        if (class_exists($validator)) {
            if ($method === null) {
                throw new SchemaException($schema::class . " - No \$method parameter specified");
            } elseif (method_exists($validator, $method)) {
                return $validator::$method($value);
            } else {
                throw new SchemaException($schema::class . " - Validator has no such method: $method");
            }
        }

        if (method_exists($schema, $validator)) {
            return $schema->$validator($value);
        }

        return $validator($value);
    }

    public function getErrorMessage($value): string
    {
        return $this->errorMessage ?: "{$this->method}({$this->stringify($value)})";
    }
}
