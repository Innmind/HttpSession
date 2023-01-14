<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Session;

use Innmind\HttpSession\Session\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testInterface()
    {
        $name = Name::maybe('PHPSESSID')->match(
            static fn($name) => $name,
            static fn() => null,
        );

        $this->assertSame('PHPSESSID', $name->toString());
    }

    public function testReturnNothingWhenInvalidFormat()
    {
        $this->assertNull(Name::maybe('foo.bar')->match(
            static fn($name) => $name,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenEmptyName()
    {
        $this->assertNull(Name::maybe('')->match(
            static fn($name) => $name,
            static fn() => null,
        ));
    }
}
