<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Session;

use Innmind\HttpSession\{
    Session\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testInterface()
    {
        $name = new Name('PHPSESSID');

        $this->assertSame('PHPSESSID', (string) $name);
    }

    public function testThrowWhenInvalidFormat()
    {
        $this->expectException(DomainException::class);

        new Name('foo.bar');
    }

    public function testThrowWhenEmptyName()
    {
        $this->expectException(DomainException::class);

        new Name('');
    }
}
