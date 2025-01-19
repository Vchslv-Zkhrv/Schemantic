<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class Env extends Schema
{
    public string $secret;
    public string $mode;
    public int $ttl;

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'secret' => 'APP_SECRET',
            'mode' => 'APP_ENV',
            'ttl' => 'REDIS_TTL'
        ];
    }

    public function __construct(
        string $secret,
        string $mode,
        int $ttl
    )
    {
        $this->secret = $secret;
        $this->mode = $mode;
        $this->ttl = $ttl;
    }
}
