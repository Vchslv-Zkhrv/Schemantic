<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Objects;

enum StatusEnum: string
{
    case ACTIVE = 'a';
    case BANNED = 'b';
    case CANCELED = 'c';
    case DELETED = 'd';
}
