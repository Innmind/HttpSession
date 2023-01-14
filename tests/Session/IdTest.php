<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Session;

use Innmind\HttpSession\Session\Id;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    public function testInterface()
    {
        $id = Id::maybe('b5vcgpppij52r1krp1tbs26i27')->match(
            static fn($id) => $id,
            static fn() => null,
        );

        $this->assertSame('b5vcgpppij52r1krp1tbs26i27', $id->toString());
    }

    public function testReturnNothingWhenInvalidFormat()
    {
        $this->assertNull(Id::maybe('foo.bar')->match(
            static fn($id) => $id,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenEmptyId()
    {
        $this->assertNull(Id::maybe('')->match(
            static fn($id) => $id,
            static fn() => null,
        ));
    }
}
