<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Objects;

use Schemantic\Schema;

class WeatherConditions extends Schema
{
    public function __construct(
        public readonly int $temperature,
        public readonly bool $rain = false,
        public readonly bool $snow = false,
        public readonly bool $hail = false,
        public readonly bool $mist = false,
    ) {
    }
}
