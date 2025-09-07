<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Objects;

enum TimesOfDay: string
{
    case MORNING = 'morning';
    case MIDDAY  = 'midday';
    case EVENING = 'evening';
    case NIGHT   = 'night';
}
