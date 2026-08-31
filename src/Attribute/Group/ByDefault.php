<?php

namespace Schemantic\Attribute\Group;

use Attribute;
use Schemantic\Exception\SchemaException;

/**
 * Default group of attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Grou[
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class ByDefault extends Group
{
    /**
     * ByDefault constructor
     *
     * @param GroupingAttributeInterface[] ...$attributes attributes in group. No more than one of each class
     *
     * @throws SchemaException
     */
    public function __construct(GroupingAttributeInterface ...$attributes)
    {
        parent::__construct(static::DEFAULT_GROUP_NAME, ...$attributes);
    }
}
