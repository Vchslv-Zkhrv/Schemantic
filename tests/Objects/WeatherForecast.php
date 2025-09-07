<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Objects;

use Schemantic\Attribute\ArrayOf;
use Schemantic\Attribute\DateTimeFormat;
use Schemantic\Attribute\EnumArrayKey;
use Schemantic\Schema;

class WeatherForecast extends Schema
{
    /**
     * @param \DateTimeImmutable                  $day
     * @param array<TimesOfDay,WeatherConditions> $conditions
     */
    public function __construct(
        #[DateTimeFormat('Y-m-d')]
        public readonly \DateTimeImmutable $day,
        //
        #[ArrayOf(WeatherConditions::class)]
        #[EnumArrayKey(TimesOfDay::class)]
        public readonly array $conditions,
    ) {
    }
}
