<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Objects;

/**
 * May be Doctrine entity
 * Object to try methods fromObject, buildObject, updateObject
 * @property int $age
 */
class ShortUser
{
    private bool $isActive = true;
    public string $fname;
    public string $lname;
    public \DateTimeInterface $birthday;

    public function __get(string $name)
    {
        if ($name == 'age') {
            return (new \DateTime('now', new \DateTimeZone('UTC')))->diff($this->birthday)->y;
        }
    }

    public function __set(string $name, $value): void
    {
        if ($name == 'age') {
            $this->birthday = new \DateTime("-$value years", new \DateTimeZone('UTC'));
        }
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getActive(): bool
    {
        return $this->isActive;
    }
}
