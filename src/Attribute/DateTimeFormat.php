<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * @deprecated use Schemantic\Attribute\Chrono\DateTimeFormat instead
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class DateTimeFormat extends \Schemantic\Attribute\Chrono\DateTimeFormat
{
}
