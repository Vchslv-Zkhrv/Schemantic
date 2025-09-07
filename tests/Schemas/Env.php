<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class Env extends Schema
{
    #[Alias('APP_SECRET')]
    public string $secret;

    #[Alias('APP_ENV')]
    public string $mode;

    #[Alias('REDIS_TTL')]
    public int $ttl;

    public function __construct(
        string $secret,
        string $mode,
        int $ttl
    ) {
        $this->secret = $secret;
        $this->mode = $mode;
        $this->ttl = $ttl;
    }
}
