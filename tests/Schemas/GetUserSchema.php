<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class GetUserSchema extends Schema
{
    #[Alias('fname')]
    public string $firstname;

    #[Alias('lname')]
    public string $lastname;

    public int $age;

    public bool $isActive;

    public function __construct(
        string $firstname,
        string $lastname,
        int $age,
        bool $isActive
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->age = $age;
        $this->isActive = $isActive;
    }
}
