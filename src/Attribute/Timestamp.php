<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * @deprecated use Schemantic\Attribute\Chrono\Timestamp instead
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class Timestamp extends \Schemantic\Attribute\Chrono\Timestamp
{
}
