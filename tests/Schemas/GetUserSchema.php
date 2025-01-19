<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class GetUserSchema extends Schema
{
    public string $firstname;
    public string $lastname;
    public int $age;
    public bool $isActive;

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
       return [
            'firstname' => 'fname',
            'lastname' => 'lname',
       ];
    }

    public function __construct(
        string $firstname,
        string $lastname,
        int $age,
        bool $isActive
    )
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->age = $age;
        $this->isActive = $isActive;
    }
}
