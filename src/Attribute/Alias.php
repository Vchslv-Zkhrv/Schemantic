<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * @deprecated use Schemantic\Attribute\Alias\Alias instead
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class Alias extends \Schemantic\Attribute\Alias\Alias
{
}
