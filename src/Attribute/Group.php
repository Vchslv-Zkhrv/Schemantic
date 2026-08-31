<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * @deprecated use Schemantic\Attribute\Group\Group instead
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class Group extends \Schemantic\Attribute\Group\Group
{
}
