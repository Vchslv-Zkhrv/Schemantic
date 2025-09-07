<?php

namespace Schemantic\Attribute\Validate;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to set a property validation callback
 *
 * @extends BaseValidation<mixed>
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER|Attribute::IS_REPEATABLE)]
class Validate extends BaseValidation
{
    /**
     * @param string      $method       name of schema's validation method
     * @param string|null $errorMessage error message
     */
    public function __construct(
        public readonly string $method,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public function check($value, SchemaInterface $schema): bool
    {
        $method = $this->method;
        return $schema->$method($value);
    }

    public function getErrorMessage($value): string
    {
        return $this->errorMessage ?: "{$this->method}({$this->stringify($value)})";
    }
}
